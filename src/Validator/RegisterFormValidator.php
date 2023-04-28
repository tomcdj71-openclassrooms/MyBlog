<?php

namespace App\Validator;

use App\Helper\SecurityHelper;
use App\Manager\UserManager;

class RegisterFormValidator extends BaseValidator
{
    protected SecurityHelper $securityHelper;
    protected UserManager $userManager;

    public function __construct(UserManager $userManager, SecurityHelper $securityHelper)
    {
        parent::__construct($userManager, $securityHelper);
        $this->securityHelper = $securityHelper;
    }

    public function validate($data)
    {
        $validationRules = [
            'email' => [
                'constraints' => [
                    'required' => true, 'errorMsg', "Merci d'entrer une adresse e-mail.",
                    'unique' => ['errorMsg' => 'Cette adresse e-mail est déjà enregistrée.'],
                    'type' => 'email',
                ],
            ],
            'username' => [
                'constraints' => [
                    'required' => true, 'errorMsg', "Merci d'entrer un nom d'utilisateur.",
                    'unique' => ['errorMsg' => 'Cet utilisateur est déjà enregistré.'],
                    'length' => [
                        'min' => 2, 'minErrorMsg' => "Le nom d 'utilisateur doit contenir au moins 2 caractères.",
                        'max' => 60, 'maxErrorMsg' => "Le nom d'utilisateur ne doit pas dépasser 60 caractères.",
                    ],
                ],
            ],
            'password' => [
                'constraints' => [
                    'required' => true, 'errorMsg', "Merci d'entrer un mot de passe.",
                    'length' => [
                        'min' => 8, 'minErrorMsg' => 'Le mot de passe doit contenir au moins 8 caractères.',
                        'max' => 130, 'maxErrorMsg' => 'Le mot de passe ne doit pas dépasser 130 caractères.',
                    ],
                ],
            ],
            'passwordConfirm' => [
                'constraints' => [
                    'required' => true, 'errorMsg', 'Merci de confirmer votre mot de passe.',
                    'type' => 'compare',
                    'compareField' => 'password', 'errorMsg' => 'La confirmation du mot de passe ne correspond pas.',
                ],
            ],
            'csrfToken' => [
                'constraints' => [
                    'required' => true,
                    'type' => 'csrf',
                ],
            ],
        ];

        return $this->validateData($data, $validationRules);
    }

    protected function validateCsrfToken($token, $errorMsg)
    {
        return $this->securityHelper->checkCsrfToken('register', $token) ? '' : $errorMsg;
    }
}
