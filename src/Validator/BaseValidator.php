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
                $errors = array_merge($errors, $this->validateField($field, $data, $rules));
            }
        }

        return [
            'errors' => $errors,
            'valid' => empty($errors),
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
                    $errors[$field] = $rules['constraints']['errorMsg'] ?? '';
                }

                break;

            case 'array':
                if (!is_array($data[$field])) {
                    $errors[$field] = $rules['constraints']['errorMsg'] ?? '';
                }

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

        return $errors;
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
}
