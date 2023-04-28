<?php

declare(strict_types=1);

namespace App\Validator;

use App\Helper\SecurityHelper;
use App\Manager\UserManager;

class LoginFormValidator extends BaseValidator
{
    private UserManager $userManager;
    private SecurityHelper $securityHelper;

    public function __construct(UserManager $userManager, SecurityHelper $securityHelper)
    {
        parent::__construct($securityHelper);
        $this->userManager = $userManager;
        $this->securityHelper = $securityHelper;
    }

    public function validate(array $data): array
    {
        // Define your validation rules here
        $validationRules = [
            'email' => ['type' => 'email', 'required' => true, 'errorMsg' => 'Invalid email.'],
            'password' => ['type' => 'empty', 'required' => true, 'errorMsg' => 'Password is required.'],
            'remember' => ['type' => 'empty', 'required' => false],
            'csrfToken' => ['type' => 'csrf', 'required' => true, 'errorMsg' => 'Invalid CSRF token.'],
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
