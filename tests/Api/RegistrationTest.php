<?php

namespace App\Tests\Api;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use App\Repository\UserRepository;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\HttpClient\ResponseInterface;

/**
 * Funkční testy registračního endpointu (API Platform resource App\ApiResource\Register).
 */
#[Group('auth')]
class RegistrationTest extends ApiTestCase
{
    // API Platform 4.1: explicitně bootujeme kernel při createClient() (default se v 5.0 změní).
    protected static ?bool $alwaysBootKernel = true;

    private const API_URL = '/api/register';

    /** Validní vstup používaný jako základ; jednotlivé testy si přepisují konkrétní pole. */
    private const VALID = [
        'nickname' => 'pepan',
        'email' => 'pepa@test.cz',
        'password' => 'Kombajn_88_Traktor!',
        'passwordRepeat' => 'Kombajn_88_Traktor!',
    ];

    public function testValidRegistration(): void
    {
        $client = static::createClient();
        $client->request('POST', self::API_URL, ['json' => self::VALID]);

        $this->assertResponseStatusCodeSame(Response::HTTP_CREATED);

        $user = $this->users()->findOneBy(['nickname' => 'pepan']);
        $this->assertNotNull($user, 'Uživatel nebyl uložen.');
        $this->assertSame('pepa@test.cz', $user->getEmail());
        $this->assertNotSame(self::VALID['password'], $user->getPassword(), 'Heslo musí být zahashované.');
    }

    public function testRegistrationWithoutEmailSucceeds(): void
    {
        // email je dobrovolný, po případné změně nutno změnit tento test 
        $data = self::VALID;
        unset($data['email']);

        $client = static::createClient();
        $client->request('POST', self::API_URL, ['json' => $data]);

        $this->assertResponseStatusCodeSame(Response::HTTP_CREATED);

        $user = $this->users()->findOneBy(['nickname' => 'pepan']);
        $this->assertNotNull($user);

        // zde i message změnit
        $this->assertNull($user->getEmail(), 'Email měl zůstat prázdný.');
    }

    #[DataProvider('invalidFieldProvider')]
    public function testValidationErrors(array $override, string $propertyPath, string $message): void
    {
        $client = static::createClient();
        $response = $client->request('POST', self::API_URL, ['json' => array_merge(self::VALID, $override)]);

        $this->assertResponseStatusCodeSame(Response::HTTP_UNPROCESSABLE_ENTITY);
        $this->assertHasViolation($response, $propertyPath, $message);
        $this->assertSame(0, $this->users()->count([]), 'Nevalidní registrace nesmí nic uložit.');
    }

    /**
     * @return array<string, array{0: array<string, mixed>, 1: string, 2: string}>
     */
    public static function invalidFieldProvider(): array
    {
        return [
            // nickname
            'missing_nickname' => [['nickname' => ''], 'nickname', 'missing_nickname'],
            'short_nickname'   => [['nickname' => 'pep'], 'nickname', 'low_length_nickname'],
            'long_nickname'    => [['nickname' => str_repeat('a', 181)], 'nickname', 'high_length_nickname'],
            // email
            'invalid_email'    => [['email' => 'neni-email'], 'email', 'invalid_email'],
            // password
            'missing_password' => [['password' => '', 'passwordRepeat' => ''], 'password', 'missing_password'],
            'short_password'   => [['password' => 'Komba1!', 'passwordRepeat' => 'Komba1!'], 'password', 'password_short'],
            'weak_password'    => [['password' => '123456789', 'passwordRepeat' => '123456789'], 'password', 'password_weak'],
            // shoda hesel
            'password_no_match' => [['passwordRepeat' => 'JineHeslo_99!'], '', 'password_no_match'],
        ];
    }

    public function testDuplicateUserIsRejected(): void
    {
        $client = static::createClient();

        // první registrace projde
        $client->request('POST', self::API_URL, ['json' => self::VALID]);
        $this->assertResponseStatusCodeSame(Response::HTTP_CREATED);

        // druhá se stejným nickem i emailem → 422 s unikátními chybami
        $response = $client->request('POST', self::API_URL, ['json' => self::VALID]);
        $this->assertResponseStatusCodeSame(Response::HTTP_UNPROCESSABLE_ENTITY);
        $this->assertHasViolation($response, 'nickname', 'not_unique_nickname');
        $this->assertHasViolation($response, 'email', 'not_unique_email');

        $this->assertSame(1, $this->users()->count([]), 'Duplicita nesmí vytvořit druhého uživatele.');
    }

    public function testEmptyBodyReturnsUnprocessable(): void
    {
        $client = static::createClient();
        $response = $client->request('POST', self::API_URL, [
            'headers' => ['Content-Type' => 'application/json'],
            'body' => '{}',
        ]);

        $this->assertResponseStatusCodeSame(Response::HTTP_UNPROCESSABLE_ENTITY);
        $this->assertHasViolation($response, 'nickname', 'missing_nickname');
        $this->assertHasViolation($response, 'password', 'missing_password');
        $this->assertSame(0, $this->users()->count([]));
    }

    public function testMalformedJsonReturnsBadRequest(): void
    {
        $client = static::createClient();
        $client->request('POST', self::API_URL, [
            'headers' => ['Content-Type' => 'application/json'],
            'body' => '{"nickname": "pepan",', // úmyslně rozbitý JSON
        ]);

        $this->assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
        $this->assertSame(0, $this->users()->count([]));
    }

    public function testRegisterRouteOnlyAllowsPost(): void
    {
        static::createClient()->request('GET', self::API_URL);

        $this->assertResponseStatusCodeSame(Response::HTTP_METHOD_NOT_ALLOWED);
    }

    private function users(): UserRepository
    {
        return static::getContainer()->get(UserRepository::class);
    }

    /**
     * Najde v odpovědi konkrétní violation podle propertyPath + message (nezávisle na pořadí).
     */
    private function assertHasViolation(ResponseInterface $response, string $propertyPath, string $message): void
    {
        $violations = $response->toArray(false)['violations'] ?? [];

        foreach ($violations as $violation) {
            if (($violation['propertyPath'] ?? null) === $propertyPath
                && ($violation['message'] ?? null) === $message) {
                $this->addToAssertionCount(1);

                return;
            }
        }

        $this->fail(sprintf(
            'Chybí violation [propertyPath="%s", message="%s"]. Vráceno: %s',
            $propertyPath,
            $message,
            json_encode($violations, JSON_UNESCAPED_UNICODE),
        ));
    }
}
