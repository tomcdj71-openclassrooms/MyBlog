<?php

declare(strict_types=1);

namespace App\Validator;

use App\Helper\SecurityHelper;
use App\Manager\UserManager;

class LoginFormValidator extends BaseValidator
{
    protected SecurityHelper $securityHelper;
    protected UserManager $userManager;

    public function __construct(UserManager $userManager, SecurityHelper $securityHelper)
    {
        parent::__construct($userManager, $securityHelper);
        $this->userManager = $userManager;
        $this->securityHelper = $securityHelper;
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
                ],
            ],
        ];

        return $this->validateData($data, $validationRules);
    }

    public function shouldRemember(array $data): bool
    {
        return isset($data['remember']) && 'true' === $data['remember'];
    }

    protected function validateCsrfToken($token, $errorMsg)
    {
        return $this->securityHelper->checkCsrfToken('login', $token) ? '' : $errorMsg;
    }
}
