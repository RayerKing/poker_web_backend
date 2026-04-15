<?php
// src/Controller/ApiTestController.php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

class ApiTestController extends AbstractController
{
    #[Route('/api/hello', name: 'api_hello', methods: ['GET'])]
    public function index(): JsonResponse
    {
        return $this->json([
            'message' => 'Ahoj z backendu! ZASE!!!',
            'status' => 'success',
            'poker_tip' => 'Nikdy nehraj Every Hand!'
        ]);
    }
}