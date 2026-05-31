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
use Psr\Log\LoggerInterface;

#[Route('/api/register', methods: ['POST'])]
class RegisterController extends AbstractController 
{
    public function __invoke(
        Request $request, 
        ValidatorInterface $validator, 
        UserPasswordHasherInterface $hash,
        EntityManagerInterface $em,
        LoggerInterface $logger,
        ): JsonResponse
    {
        $type = $request->getContentTypeFormat();
        
        $error = [];
        $validationErrorText = 'Validation Failed';

        if ($type !== 'json') {
            $error[] = [
                'property' => 'format',
                'message' => 'The format must be type of json.'
            ];

            return $this->vratResponseJsonError(Response::HTTP_UNSUPPORTED_MEDIA_TYPE, 'format_error', 'Invalid format type.', $error);
        }

        $data = $request->toArray();

        if ($data['password'] !== $data['password_repeat']) {
            $error[] = [
                'property' => 'password',
                'message' => 'password_no_match'
            ];
            return $this->vratResponseJsonError(Response::HTTP_UNPROCESSABLE_ENTITY, 'invalid_password', 'Passwords doesn´t match.', $error, $validationErrorText);
        }

        $user = new User();
        
        $user->setEmail($data['email'])
            ->setNickname($data['nickname'])
            ->setPassword($data['password']);

        $errors = $validator->validate($user);

        if (count($errors) > 0) {
            $vystup = [];
    
            foreach ($errors as $error) {
                $strukturaChyby = [
                    'property' => $error->getPropertyPath(),
                    'message' => $error->getMessage()
                ];
                $vystup[] = $strukturaChyby;
            }

            return $this->vratResponseJsonError(Response::HTTP_UNPROCESSABLE_ENTITY, 'validation_error', 'One or more fields contain invalid data.', $vystup, $validationErrorText);
        }

        $hashedPassword = $hash->hashPassword($user, $user->getPassword());
        
        $user->setPassword($hashedPassword);

        try {
            $em->persist($user);
            $em->flush();
        } catch (Exception $e) {
            $logger->error('Při registraci se něco nepovedlo.', ['exception' => $e]);
            return $this->vratResponseJsonError(Response::HTTP_INTERNAL_SERVER_ERROR, 'server_error', 'An unexpected error occurred on our server. Please try again later.');
        }
        
        return $this->vratResponseJsonSuccess(Response::HTTP_CREATED, 'registration_success');
    }

    private function vratResponseJsonError(int $code, string $typeError, string $detailError = '', array $errorList = [], string $titleError = 'An error occurred'): JsonResponse
    {
        return $this->json([
            'status' => $code,
            'type' => $typeError,
            'title' => $titleError,
            'detail' => $detailError,
            'errors' => $errorList
        ], $code);        
    }

    private function vratResponseJsonSuccess(int $code, string $type): JsonResponse
    {
        return $this->json([
                'status' => $code,
                'type' => $type
            ], $code);
    }
}   