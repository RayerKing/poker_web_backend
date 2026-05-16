<?php

namespace App\Tests\Controller;


use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

#[Group('auth')]
class RegistrationControllerTest extends WebTestCase
{

    const API_URL = '/api/register';

    const ERROR_MESSAGE = 'Uživatel se neměl zapsat do databáze!';

    /**
     * Testuje registraci uživatele s validními daty
     * @return void
     */
    #[Group('validRegister')]
    public function testRegistrationForm(): void
    {
        $client = static::createClient();

        // TADY vkládáš konkrétní JSON data
        $data = [
            'nickname' => 'testovaci_pepa',
            'email' => 'pepa@test.cz',
            'password' => 'Kombajn_88_Traktor!',
            'password_repeat' => 'Kombajn_88_Traktor!'
        ];

        $client->request(
            'POST',
            self::API_URL,
            [], // parametry (pro GET)
            [], // soubory
            ['CONTENT_TYPE' => 'application/json'], // HLAVIČKA
            json_encode($data) // TADY JE TO TĚLO (JSON)
        );

        $container = static::getContainer();

        $userRepository = $container->get(\App\Repository\UserRepository::class);

        $nickname = $userRepository->findOneBy(['nickname' => 'testovaci_pepa']);

        $this->assertNotNull($nickname, 'Uživatel nenalezen');
        $this->assertSame('testovaci_pepa', $nickname->getNickname());
        $this->assertNotSame('Kombajn_88_Traktor!', $nickname->getPassword());

        // Kontrola, že se vrátil status 200
        $this->assertResponseIsSuccessful();

        // Kontrola obsahu JSONu
        $responseContent = $client->getResponse()->getContent();
        $this->assertJson($responseContent);
        
        $responseData = json_decode($responseContent, true);
        $this->assertTrue($responseData['success']);
        $this->assertEquals('success', $responseData['message']);
    }

    /**
     * Test, kontroluje nevalidní formáty u nickname a unique nickname a email
     *
     * @return void
     */
    #[Group('invalidNicknames')]
    #[DataProvider('invalidNicknames')]
    public function testInvalidNickname(string $status, array $data, string|array $message): void
    {
        $client = static::createClient();

        $client->request('POST', 
            self::API_URL, 
            [], 
            [], 
            ['CONTENT_TYPE' => 'application/json'], // HLAVIČKA
            json_encode($data)
            );

        $response = $client->getResponse()->getContent();
       // $this->assertEquals('ahoj', $response);
        $this->assertJson($response);

        $responseData = json_decode($response, true);

        $container = static::getContainer();

        $userRepository = $container->get(\App\Repository\UserRepository::class);

        if ($status !== 'exists') {
            
            if (count($responseData['message']) !== 1) {
                $this->fail('Během testu se spustilo více chyb, nebo žádná!');
            }

            foreach ($responseData['message'] as $error) {
                if ($error['property'] == 'nickname') {
                $errorMessage = $error['message'];
                }
            }

            $this->assertFalse($responseData['success']);
            $this->assertResponseStatusCodeSame(Response::HTTP_UNPROCESSABLE_ENTITY);

            if ($status == 'missing') { 
                $this->assertEquals($message, $errorMessage); 
            } else if ($status == 'short') {
                $this->assertEquals($message, $errorMessage);
            } else if ($status == 'long') {
                $this->assertEquals($message, $errorMessage);
            } else {
                $this->fail('Chybí status?');
            }

            $user = $userRepository->findOneBy(['email' => $data['email']]);

            $this->assertNull($user, self::ERROR_MESSAGE);
            
        } else {
            $this->assertResponseIsSuccessful('Uživatel nebyl zaregistrován.');

            $user = $userRepository->findOneBy(['email' => $data['email']]);

            $this->assertNotNull($user, 'Uživatel je null!');
            $this->assertSame('pepan', $user->getNickname(), 'Nickname není stejné!');

            $client->request(
                'POST',
                self::API_URL,
                [],
                [],
                ['CONTENT_TYPE' => 'application/json'],
                json_encode($data)
                );

            $response = $client->getResponse()->getContent();

            $this->assertJson($response);

            $responseData = json_decode($response, true);

            $this->assertFalse($responseData['success']);

            $this->assertResponseStatusCodeSame(Response::HTTP_UNPROCESSABLE_ENTITY);

            $users = $userRepository->findBy(['nickname' => $data['nickname']]);
            
            if (count($users) !== 1) {
                $this->fail('V databázi je více, nebo žádný uživatel s tímto nickname!');
            }

            $errorMessageEmail = $message[0];
            $errorMessageNickname = $message[1];
            
            if (count($responseData['message']) !== 2) {
                $this->fail('Při kontrole existence vzniklo více, nebo méně chyb!');
            }

            foreach ($responseData['message'] as $error) {
                if ($error['property'] == 'nickname') {
                    $this->assertEquals($errorMessageNickname, $error['message']);
                } elseif ($error['property'] == 'email') {
                    $this->assertEquals($errorMessageEmail, $error['message']);
                } else {
                    $this->fail('Přišlo jiné property!');
                }
            }
        }
        
    }

    /**
     * Dataprovider pro testInvalidNickname
     *
     * @return array
     */
    public static function invalidNicknames(): array
    {
        return [
            'missing_nickname' => [
                'status' => 'missing',
                'data' => [
                    'nickname' => '',
                    'email' => 'pepa@test.cz',
                    'password' => 'Kombajn_88_Traktor!',
                    'password_repeat' => 'Kombajn_88_Traktor!'
                ],
                'message' => 'missing_nickname'
            ],
            // min 4
            'short_nickname' => [
                'status' => 'short',
                'data' => [
                    'nickname' => 'pep',
                    'email' => 'pepa@test.cz',
                    'password' => 'Kombajn_88_Traktor!',
                    'password_repeat' => 'Kombajn_88_Traktor!'
                ],
                'message' => 'low_length_nickname'
            ],
            // max 180
            'long_nickname' => [
                'status' => 'long',
                'data' => [
                    'nickname' => 'TohleJeNaprostoSileneDlouhyNicknameKteryMaZaUkolOtestovatZdaVaseValidaceFungujeSpravneANepustiDoDatabazeZadnyRetezecKteryByMohlPrekrocitMaximalniPovolenouDelkuSloupceVPostgresuNeboNasiEntite',
                    'email' => 'pepa@test.cz',
                    'password' => 'Kombajn_88_Traktor!',
                    'password_repeat' => 'Kombajn_88_Traktor!'
                ],
                'message' => 'high_length_nickname'
            ],
            'already_exists' => [
                'status' => 'exists',
                'data' => [
                    'nickname' => 'pepan',
                    'email' => 'pepa@test.cz',
                    'password' => 'Kombajn_88_Traktor!',
                    'password_repeat' => 'Kombajn_88_Traktor!'
                ],
                'message' => ['not_unique_email', 'not_unique_nickname']
            ]
        ];
    }

    /**
     * Testuje správný formát emailu
     *
     * @return void
     */
    #[Group('invalidEmail')]
    public function testInvalidEmail(): void
    {
        $client = static::createClient();

        $data = [
                    'nickname' => 'pepan',
                    'email' => 'pepatest.cz',
                    'password' => 'Kombajn_88_Traktor!',
                    'password_repeat' => 'Kombajn_88_Traktor!'
                ];

        $client->request(
            'POST',
            self::API_URL,
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($data)
        );

        $response = $client->getResponse()->getContent();

        $this->assertJson($response);

        $this->assertResponseStatusCodeSame(Response::HTTP_UNPROCESSABLE_ENTITY);

        $responseData = json_decode($response, true);

        if (count($responseData['message']) !== 1) {
            $this->fail('Je více, nebo žádná chyba!');
        }
        
        $this->assertEquals('invalid_email', $responseData['message'][0]['message']);

        $container = static::getContainer();

        $userRepository = $container->get(\App\Repository\UserRepository::class);

        $user = $userRepository->findBy(['nickname' => 'pepan']);
        
        $this->assertEmpty($user, 'Uživatel nalezen!');
    }

    /**
     * Test se špatným heslem
     *
     * @param string $status
     * @param array $data
     * @param string $message
     * @return void
     */
    #[Group('invalidPassword')]
    #[DataProvider('invalidPasswords')]
    public function testInvalidPassword(string $status, array $data, string $message): void
    {
        $client = static::createClient();

        $client->request(
            'POST',
            self::API_URL,
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($data)
        );

        $response = $client->getResponse()->getContent();

        $this->assertJson($response);

        $responseData = json_decode($response, true);

        $this->assertResponseStatusCodeSame(Response::HTTP_UNPROCESSABLE_ENTITY);
        
        if (count($responseData['message']) !== 1) {
            $this->fail('Počet chyb je větší nebo žádná.');
        }

        $this->assertEquals($message, $responseData['message'][0]['message']);

        $container = static::getContainer();

        $userRepository = $container->get(\App\Repository\UserRepository::class);

        $user = $userRepository->findBy(['nickname' => 'pepan']);
        
        $this->assertEmpty($user, 'Uživatel nalezen!');
    }

    /**
     * DataProvider pro testInvalidPassword
     *
     * @return array
     */
    public static function invalidPasswords(): array
    {
        return [
            'missing' => [
                'status' => 'missing',
                'data' => [
                    'nickname' => 'pepan',
                    'email' => 'pepa@test.cz',
                    'password' => '',
                    'password_repeat' => ''
                ],
                'message' => 'missing_password'
            ],
            'short' => [
                'status' => 'short',
                'data' => [
                    'nickname' => 'pepan',
                    'email' => 'pepa@test.cz',
                    'password' => 'Kombajn',
                    'password_repeat' => 'Kombajn'
                ],
                'message' => 'password_short'
            ],
            'weak' => [
                'status' => 'weak',
                'data' => [
                    'nickname' => 'pepan',
                    'email' => 'pepa@test.cz',
                    'password' => '123456789',
                    'password_repeat' => '123456789'
                ],
                'message' => 'password_weak'
            ]
        ];
    }

    /**
     * Test kontroluje, že hesla nejsou shodná
     *
     * @return void
     */
    #[Group('invalidMatchPassword')]
    public function testInvalidMatchingPasswords(): void
    {
        $client = static::createClient();

        $data = [
                    'nickname' => 'pepan',
                    'email' => 'pepatest.cz',
                    'password' => 'Kombajn_88_Traktor!',
                    'password_repeat' => 'Kombajn_88_Traktor!s'
                ];

        $client->request(
            'POST',
            self::API_URL,
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($data)
        );

        $response = $client->getResponse()->getContent();

        $this->assertJson($response);

        $this->assertResponseStatusCodeSame(Response::HTTP_UNPROCESSABLE_ENTITY);

        $responseData = json_decode($response, true);

        if (count($responseData['message']) !== 1) {
            $this->fail('Počet chyb je větší, nebo 0');
        }

        $this->assertEquals('password_no_match', $responseData['message'][0]['message']);

        $container = static::getContainer();

        $userRepository = $container->get(\App\Repository\UserRepository::class);

        $user = $userRepository->findBy(['nickname' => 'pepan']);
        
        $this->assertEmpty($user, 'Uživatel nalezen!');
    }

    #[Group('methodNotAllowed')]
    public function testRegisterRouteOnlyAllowsPost(): void
    {
        $client = static::createClient();

        $client->request('GET', self::API_URL);

        $this->assertResponseStatusCodeSame(Response::HTTP_METHOD_NOT_ALLOWED);
    }
}
