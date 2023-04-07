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

        if (empty($data['username'])) {
            $errors['username'] = 'Please enter a username';
        }

        if (empty($data['password'])) {
            $errors['password'] = 'Please enter a password';
        }

        if (empty($errors)) {
            $user = $this->userManager->findBy(['username' => $data['username']]);
            // Persist the user in the session

            if (!$user) {
                $errors['username'] = 'This username does not exist';
            } elseif (!password_verify($data['password'], $user->getPassword())) {
                $errors['password'] = 'This password is incorrect';
            }
        }

        return $errors;
    }
}
