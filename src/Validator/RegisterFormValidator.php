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
            'email' => ['type' => 'email', 'errorMsg' => 'Veuillez inscrire une adresse email valide!', 'required' => true],
            'username' => ['type' => 'empty', 'errorMsg' => "Merci d'entrer un nom d'utilisateur.", 'required' => true],
            'password' => ['type' => 'empty', 'errorMsg' => "Merci d'entrer un mot de passe.", 'required' => true],
            'passwordConfirm' => ['type' => 'confirm', 'compareField' => 'password', 'errorMsg' => 'La confirmation du mot de passe ne correspond pas.', 'required' => true],
            'csrf_token' => ['type' => 'csrf', 'errorMsg' => 'Jeton CSRF invalide.', 'required' => true],
        ];

        return $this->validateData($data, $validationRules);
    }

    protected function validateCsrfToken($token, $errorMsg)
    {
        return $this->securityHelper->checkCsrfToken('register', $token) ? '' : $errorMsg;
    }
}
