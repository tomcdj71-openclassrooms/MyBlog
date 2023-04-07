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

        if (empty($data['username'])) {
            $valid = false;
            $errors['username'] = 'Please choose a username.';
        }

        if (empty($data['password'])) {
            $valid = false;
            $errors['password'] = 'Please enter your password!';
        }

        if ($data['password'] !== $data['passwordConfirm']) {
            $valid = false;
            $errors['passwordConfirm'] = 'Please confirm your password!';
        }

        return [
            'valid' => $valid,
            'errors' => $errors,
        ];
    }
}
