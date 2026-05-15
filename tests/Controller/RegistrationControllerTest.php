<?php

namespace App\Tests\Controller;

use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use PHPUnit\Framework\Attributes\Group;

#[Group('auth')]
class RegistrationControllerTest extends WebTestCase
{
    /**
     * Testuje registraci uživatele
     *
     * @return void
     */
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
            '/api/register',
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
}
