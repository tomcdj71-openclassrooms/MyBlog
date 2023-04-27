<?php

namespace App\Validator;

abstract class BaseValidator
{
    protected function validateData($data, $validationRules)
    {
        $errors = [];

        foreach ($validationRules as $field => $rule) {
            if (isset($data[$field]) || $rule['required']) {
                $validationResult = $this->validateField($data, $field, $rule);
                if (!$validationResult['valid']) {
                    $errors[$field] = $validationResult['errorMsg'];
                }
            }
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors,
        ];
    }

    protected function validateField($data, $field, $rule)
    {
        $error = '';

        switch ($rule['type']) {
            case 'email':
                $error = $this->validateEmail($data[$field], $rule['errorMsg']);

                break;

            case 'empty':
                $error = $this->validateNotEmpty($data[$field], $rule['errorMsg']);

                break;

            case 'confirm':
                $error = $this->validateConfirm($data[$field], $data[$rule['compareField']], $rule['errorMsg']);

                break;

            case 'csrf':
                $error = call_user_func([$this, 'validateCsrfToken'], $data[$field], $rule['errorMsg']);

                break;

            default:
                $error = 'Règle de validation inconnue';
        }

        return ['valid' => empty($error), 'errorMsg' => $error];
    }

    protected function validateEmail($email, $errorMsg)
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL) ? '' : $errorMsg;
    }

    protected function validateNotEmpty($value, $errorMsg)
    {
        return !empty($value) ? '' : $errorMsg;
    }

    protected function validateConfirm($value1, $value2, $errorMsg)
    {
        return $value1 === $value2 ? '' : $errorMsg;
    }
}
