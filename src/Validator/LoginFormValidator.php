<?php

declare(strict_types=1);

namespace App\Validator;

use App\Manager\UserManager;
use App\Router\Session;
use App\Service\CsrfTokenService;

class LoginFormValidator extends BaseValidator
{
    protected Session $session;
    protected UserManager $userManager;
    protected CsrfTokenService $csrfTokenService;

    public function __construct(UserManager $userManager, Session $session, CsrfTokenService $csrfTokenService)
    {
        parent::__construct($userManager, $session, $csrfTokenService);
        $this->csrfTokenService = $csrfTokenService;
    }

    public function validate(array $data): array
    {
        $validationRules = [
            'email' => [
                'constraints' => [
                    'required' => true, 'errorMsg' => 'Cet email ne correspond pas à un utilisateur.',
                    'type' => 'email',
                ],
            ],
            'password' => [
                'constraints' => [
                    'required' => true, 'errorMsg' => 'Ce mot de passe est invalide.',
                ],
            ],
            'remember' => [
                'constraints' => [
                    'required' => false,
                ],
            ],
            'csrfToken' => [
                'constraints' => [
                    'required' => true,
                    'type' => 'csrf',
                    'csrfKey' => 'login',
                ],
            ],
        ];

        return $this->validateData($data, $validationRules);
    }

    public function shouldRemember(array $data): bool
    {
        return isset($data['remember']) && 'true' === $data['remember'];
    }
}
