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
    }

    public function validate(array $data): array
    {
        $validationRules = [
            'email' => [
                'constraints' => [
                    'required' => true,
                    'type' => 'email',
                ],
            ],
            'password' => [
                'constraints' => [
                    'required' => true,
                    'type' => 'password',
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
}
