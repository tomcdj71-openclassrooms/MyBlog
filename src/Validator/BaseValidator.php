<?php

declare(strict_types=1);

namespace App\Validator;

use App\Helper\SecurityHelper;
use App\Manager\UserManager;

abstract class BaseValidator
{
    protected UserManager $userManager;
    protected SecurityHelper $securityHelper;

    public function __construct(UserManager $userManager = null, SecurityHelper $securityHelper)
    {
        $this->userManager = $userManager;
        $this->securityHelper = $securityHelper;
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
                $error = $this->validateCsrfToken($data[$field], $rules['constraints']['errorMsg'] ?? '');
                if ($error) {
                    $errors[$field] = $error;
                }

                break;

            case 'compare':
                if ($data[$field] !== $data[$rules['constraints']['compareField']]) {
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

    abstract protected function validateCsrfToken($token, $errorMsg);
}
