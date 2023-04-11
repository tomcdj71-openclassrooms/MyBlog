<?php

namespace App\Validator;

class RegisterFormValidator
{
    public function validate($data)
    {
        $errors = [];
        $valid = true;

        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $valid = false;
            $errors['email'] = 'Please enter a valid email address!';
        }

        if (!isset($data['username']) || empty($data['username'])) {
            $valid = false;
            $errors['username'] = 'Please enter a username.';
        }

        if (!isset($data['password']) || empty($data['password'])) {
            $valid = false;
            $errors['password'] = 'Please enter a password.';
        }

        if (!isset($data['passwordConfirm']) || empty($data['passwordConfirm'])) {
            $valid = false;
            $errors['passwordConfirm'] = 'Please confirm your password.';
        } elseif (isset($data['password']) && $data['password'] !== $data['passwordConfirm']) {
            $valid = false;
            $errors['passwordConfirm'] = 'The password confirmation does not match.';
        }

        return [
            'valid' => $valid,
            'errors' => $errors,
        ];
    }
}
