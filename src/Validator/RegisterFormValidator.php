<?php

namespace App\Validator;

class RegisterFormValidator extends BaseValidator
{
    private $securityHelper;

    public function __construct($securityHelper)
    {
        $this->securityHelper = $securityHelper;
    }

    public function validate($data)
    {
        $validationRules = [
            'email' => ['type' => 'email', 'errorMsg' => 'Please enter a valid email address!', 'required' => true],
            'username' => ['type' => 'empty', 'errorMsg' => 'Please enter a username.', 'required' => true],
            'password' => ['type' => 'empty', 'errorMsg' => 'Please enter a password.', 'required' => true],
            'passwordConfirm' => ['type' => 'confirm', 'compareField' => 'password', 'errorMsg' => 'The password confirmation does not match.', 'required' => true],
            'csrf_token' => ['type' => 'csrf', 'errorMsg' => 'Invalid CSRF token.', 'required' => true],
        ];

        return $this->validateData($data, $validationRules);
    }

    protected function validateCsrfToken($token, $errorMsg)
    {
        return $this->securityHelper->checkCsrfToken('register', $token) ? '' : $errorMsg;
    }
}
