<?php

namespace App\ApiResource;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Post;
use App\State\RegistrationProcessor;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Constraints\Sequentially;

#[ApiResource(
    shortName: 'Registration',
    uriTemplate: '/register',
    description: 'User registration',
    operations: [
        new Post(
            processor: RegistrationProcessor::class,
            status:201,
            output:false,
        ),
    ],
)]

#[Assert\Expression(
    expression: 'this.password === this.passwordRepeat',
    message: 'password_no_match',
)]

class Register
{
    #[Sequentially([
        new Assert\NotBlank(message: "missing_nickname"),
        new Assert\Length(min: 4, max: 180, minMessage: "low_length_nickname", maxMessage: "high_length_nickname"),
    ])]
    public ?string $nickname = null;

    #[Sequentially([
        new Assert\NotBlank(message: "missing_password"),
        new Assert\Length(min:8, minMessage: "password_short"),
        new Assert\PasswordStrength(minScore: 2, message: "password_weak"),
        ]
    )]
    public ?string $password = null;

    #[Sequentially([
        new Assert\NotBlank(message: "missing_password_repeat"),
        ]
    )]
    public ?string $passwordRepeat = null;

    #[Assert\Email(message: "invalid_email")]
    public ?string $email = null;

}