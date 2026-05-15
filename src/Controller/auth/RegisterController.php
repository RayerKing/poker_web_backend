<?php
namespace App\Controller\auth;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api/register', methods: ['POST'])]
class RegisterController extends AbstractController 
{
    public function __invoke(
        Request $request, 
        ValidatorInterface $validator, 
        UserPasswordHasherInterface $hash,
        EntityManagerInterface $em
        ): JsonResponse
    {
        $type = $request->getContentTypeFormat();

        if ($type !== 'json') {
            return $this->vratResponse(false, 'invalid type format', Response::HTTP_UNSUPPORTED_MEDIA_TYPE);
        }

        $data = $request->toArray();

        if ($data['password'] !== $data['password_repeat']) {
            return $this->vratResponse(false, 'password no match', Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $user = new User();
        
        $user->setEmail($data['email'])
            ->setNickname($data['nickname'])
            ->setPassword($data['password']);

        $errors = $validator->validate($user);

        if (count($errors) > 0) {
            $vystup = [];
    
            foreach ($errors as $error) {
            $vystup[$error->getPropertyPath()] = $error->getMessage();
            }

            return $this->vratResponse(false, $vystup, Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $hashedPassword = $hash->hashPassword($user, $user->getPassword());
        
        $user->setPassword($hashedPassword);

        try {
            $em->persist($user);
            $em->flush();
        } catch (Exception $e) {
            // TODO zalogovat chybu 
            return $this->vratResponse(false, $e->getMessage(),Response::HTTP_INTERNAL_SERVER_ERROR);
        }
        
        return $this->vratResponse(true, 'success' , Response::HTTP_OK);
    }

    private function vratResponse(bool $status, string|array $message, int $code): JsonResponse
    {
        return $this->json([
            'success' => $status,
            'message' => $message
        ], $code);
    }
}   