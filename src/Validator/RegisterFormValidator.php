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
            'email' => ['type' => 'email', 'errorMsg' => 'Veuillez inscrire une adresse email valide!', 'required' => true, 'constraints' => ['unique' => ['errorMsg' => 'Cet adresse e-mail est déjà enregistré.']]],
            'username' => ['type' => 'empty', 'errorMsg' => "Merci d'entrer un nom d'utilisateur.", 'required' => true, 'constraints' => ['unique' => ['errorMsg' => 'Cet utilisateur est déjà enregistré.']]],
            'password' => ['type' => 'empty', 'errorMsg' => "Merci d'entrer un mot de passe.", 'required' => true],
            'passwordConfirm' => ['type' => 'confirm', 'compareField' => 'password', 'errorMsg' => 'La confirmation du mot de passe ne correspond pas.', 'required' => true],
            'csrfToken' => ['type' => 'csrf', 'errorMsg' => 'Jeton CSRF invalide.', 'required' => true],
        ];

        return $this->validateData($data, $validationRules);
    }

    protected function validateCsrfToken($token, $errorMsg)
    {
        return $this->securityHelper->checkCsrfToken('register', $token) ? '' : $errorMsg;
    }
}
