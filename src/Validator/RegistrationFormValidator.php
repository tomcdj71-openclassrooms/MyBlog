<?php

declare(strict_types=1);

namespace App\Validator;

use App\Manager\UserManager;
use App\Router\Session;
use App\Service\CsrfTokenService;

class RegistrationFormValidator extends BaseValidator
{
    protected Session $session;
    protected UserManager $userManager;
    protected CsrfTokenService $csrfTokenService;

    public function __construct(UserManager $userManager, Session $session, CsrfTokenService $csrfTokenService)
    {
        parent::__construct($userManager, $session, $csrfTokenService);
    }

    public function validate(array $data): array
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
                    'csrfKey' => 'register',
                ],
            ],
        ];

        return $this->validateData($data, $validationRules);
    }
}
