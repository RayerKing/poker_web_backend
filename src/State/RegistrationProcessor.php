<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use ApiPlatform\Validator\Exception\ValidationException;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class RegistrationProcessor implements ProcessorInterface
{
    public function __construct(
        private readonly UserPasswordHasherInterface $passwordHasher,
        private readonly EntityManagerInterface $entityManager,
        private readonly ValidatorInterface $validator,
    ) {}

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): mixed
    {
        
        $newUser = new User()
            ->setNickname($data->nickname)
            ->setEmail($data->email)
            ->setPassword($data->password);

        $errors = $this->validator->validate($newUser);

        if (count($errors) > 0) {
            throw new ValidationException($errors);
        }

        $passwordHashed = $this->passwordHasher->hashPassword($newUser, $newUser->getPassword());

        $newUser->setPassword($passwordHashed);

        $this->entityManager->persist($newUser);
        $this->entityManager->flush();

        return null;
    }
}