<?php

declare(strict_types=1);

namespace App\Validator;

use App\Manager\UserManager;
use App\Router\Session;
use App\Service\CsrfTokenService;

abstract class BaseValidator
{
    protected UserManager $userManager;
    protected Session $session;
    protected CsrfTokenService $csrfTokenService;

    public function __construct(UserManager $userManager, Session $session, CsrfTokenService $csrfTokenService)
    {
        $this->userManager = $userManager;
        $this->session = $session;
        $this->csrfTokenService = $csrfTokenService;
    }

    public function validateData(array $data, array $validationRules): array
    {
        $errors = [];
        foreach ($validationRules as $field => $rules) {
            if ($rules['constraints']['required'] && !isset($data[$field])) {
                $errors[$field] = $rules['constraints']['errorMsg'] ?? '';

                continue;
            }
            if (isset($data[$field])) {
                list($fieldErrors, $updatedData) = $this->validateField($field, $data, $rules);
                $errors = array_merge($errors, $fieldErrors);
                $data = $updatedData;
            }
        }

        return [
            'errors' => $errors,
            'valid' => empty($errors),
            'data' => $data,
        ];
    }

    protected function validateField(string $field, array $data, array $rules): array
    {
        $errors = [];

        switch ($rules['constraints']['type'] ?? '') {
            case 'email':
                if (!filter_var($data[$field], FILTER_VALIDATE_EMAIL)) {
                    $errors[$field] = $rules['constraints']['errorMsg'] ?? '';
                }

                break;

            case 'csrf':
                if (!$this->csrfTokenService->checkCsrfToken($rules['constraints']['csrfKey'], $data[$field])) {
                    $errors[$field] = $rules['constraints']['errorMsg'] ?? '';
                }

                break;

            case 'compare':
                if ($data[$field] !== $data[$rules['constraints']['compareField']]) {
                    $errors[$field] = $rules['constraints']['errorMsg'] ?? '';
                }

                break;

            case 'int':
                if (!is_numeric($data[$field])) {
                    $errors[$field] = 'Un nombre entier est attendu.';
                }

                break;

            case 'array':
                if (!is_array($data[$field])) {
                    $errors[$field] = $rules['constraints']['errorMsg'] ?? '';
                }

                break;

            case 'username':
                $data[$field] = $this->sanitizeExternalUsername($data[$field]);
                if (empty($data[$field])) {
                    $errors[$field] = $rules['constraints']['errorMsg'] ?? '';
                }

                break;

            case 'file':
                $errors = array_merge($errors, $this->validateFile($field, $data, $rules));

                break;
        }
        if (isset($rules['constraints']['length'])) {
            $errors = array_merge($errors, $this->validateLength($field, $data, $rules));
        }
        if (isset($rules['constraints']['unique']) && $this->userManager) {
            $user = $this->userManager->findOneBy([$field => $data[$field]]);
            if ($user) {
                $errors[$field] = $rules['constraints']['unique']['errorMsg'] ?? '';
            }
        }

        return [$errors, $data];
    }

    protected function validateLength(string $field, array $data, array $rules): array
    {
        $errors = [];
        $length = strlen($data[$field]);
        $minErrorMsg = $rules['constraints']['length']['minErrorMsg'] ?? '';
        $maxErrorMsg = $rules['constraints']['length']['maxErrorMsg'] ?? '';
        if ($length < $rules['constraints']['length']['min']) {
            $errors[$field] = $minErrorMsg;

            return $errors;
        }
        if ($length > $rules['constraints']['length']['max']) {
            $errors[$field] = $maxErrorMsg;

            return $errors;
        }

        return $errors;
    }

    /**
     * This function is used to sanitize external usernames
     * It removes all characters except letters, numbers and underscores
     * User can gives an url (ex: https://github.com/username or other urls) or a username (ex: username)
     * This function will return username only.
     */
    protected function sanitizeExternalUsername(string $username): string
    {
        $username = explode('/', $username);
        $username = end($username);

        return preg_replace('/[^a-zA-Z0-9_]/', '', $username);
    }

    protected function validateFile(string $field, array $data, array $rules): array
    {
        $errors = [];
        if (!isset($data[$field]) || UPLOAD_ERR_NO_FILE === $data[$field]['error']) {
            return $errors;
        }
        if ($rules['constraints']['required'] && !is_uploaded_file($data[$field]['tmp_name'])) {
            $errors[$field] = $rules['constraints']['errorMsg'] ?? '';

            return $errors;
        }
        $fileType = pathinfo($data[$field]['name'], PATHINFO_EXTENSION);
        if (!in_array($fileType, $rules['constraints']['fileType']['value'])) {
            $errors[$field] = $rules['constraints']['fileType']['errorMsg'];

            return $errors;
        }
        if ($data[$field]['size'] > $rules['constraints']['fileSize']['value']) {
            $errors[$field] = $rules['constraints']['fileSize']['errorMsg'];

            return $errors;
        }

        return $errors;
    }
}
