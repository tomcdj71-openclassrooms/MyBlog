<?php

declare(strict_types=1);

namespace App\Validator;

class EditProfileFormValidator
{
    private $securityHelper;

    public function __construct($securityHelper)
    {
        $this->securityHelper = $securityHelper;
    }

    public function validate(array $data): array
    {
        $valid = true;
        $errors = [];

        $urlRegex = [
            'twitter' => '/^(https?:\/\/)?(www\.)?twitter\.com\/([a-zA-Z0-9_]{1,15})$/',
            'facebook' => '/^(https?:\/\/)?(www\.)?facebook\.com\/([a-zA-Z0-9_]{1,15})$/',
            'github' => '/^(https?:\/\/)?(www\.)?github\.com\/([a-zA-Z0-9_]{1,15})$/',
            'linkedin' => '/^(https?:\/\/)?(www\.)?linkedin\.com\/([a-zA-Z0-9_]{1,15})$/',
        ];

        $validationRules = [
            'email' => ['type' => 'email', 'errorMsg' => 'Veuillez inscrire une adresse email valide!'],
            'firstName' => ['type' => 'length', 'length' => 60, 'errorMsg' => 'Le prénom ne doit pas dépasser 60 caractères.'],
            'lastName' => ['type' => 'length', 'length' => 60, 'errorMsg' => 'Le nom de famille ne doit pas dépasser 60 caractères.'],
            'bio' => ['type' => 'length', 'length' => 500, 'errorMsg' => 'La biographie ne peut pas dépasser 500 caractères.'],
        ];

        foreach ($validationRules as $field => $rule) {
            if (isset($data[$field])) {
                switch ($rule['type']) {
                    case 'email':
                        if (!filter_var($data[$field], FILTER_VALIDATE_EMAIL)) {
                            $valid = false;
                            $errors[$field] = $rule['errorMsg'];
                        }

                        break;

                    case 'length':
                        if (strlen($data[$field]) > $rule['length']) {
                            $valid = false;
                            $errors[$field] = $rule['errorMsg'];
                        }

                        break;
                }
            }
        }

        foreach ($urlRegex as $key => $regex) {
            if (isset($data[$key]) && !empty($data[$key])) {
                if (preg_match($regex, $data[$key], $matches)) {
                    $parts = explode('/', $matches[3]);
                    $data[$key] = end($parts);
                }
            }
        }

        if (isset($_FILES['avatar']) && UPLOAD_ERR_OK === $_FILES['avatar']['error'] && !empty($_FILES['avatar']['name'])) {
            $postData['avatar'] = $_FILES['avatar'];
        }

        if (!isset($data['csrfToken'])) {
            $valid = false;
            $errors['csrfToken'] = 'CSRF token missing.';
        }
        if (!$this->securityHelper->checkCsrfToken('editProfile', $data['csrfToken'])) {
            $valid = false;
            $errors['csrfToken'] = 'Jeton CSRF invalide.';
        }

        return [
            'valid' => $valid,
            'errors' => $errors,
            'data' => $data,
        ];
    }

    protected function validateCsrfToken($token, $errorMsg)
    {
        return [
            'valid' => $this->securityHelper->checkCsrfToken('comment', $token),
            'errorMsg' => $errorMsg,
        ];
    }
}
