<?php

declare(strict_types=1);

namespace App\Validator;

use App\Manager\UserManager;

class LoginFormValidator
{
    private $userManager;

    public function __construct(UserManager $userManager)
    {
        $this->userManager = $userManager;
    }

    public function validate(array $data): array
    {
        $errors = [];

        if (empty($data['email'])) {
            $errors['email'] = 'Please enter a email address';
        }

        if (empty($data['password'])) {
            $errors['password'] = 'Please enter a password';
        }

        if (empty($errors)) {
            $user = $this->userManager->findBy(['email' => $data['email']]);
            // Persist the user in the session

            if (!$user) {
                $errors['username'] = 'This username does not exist';
            } elseif (!password_verify($data['password'], $user->getPassword())) {
                $errors['password'] = 'This password is incorrect';
            }
        }

        return $errors;
    }

    public function shouldRemember(array $data): bool
    {
        return isset($data['remember']) && 'true' === $data['remember'];
    }
}
