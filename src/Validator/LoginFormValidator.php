<?php

declare(strict_types=1);

namespace App\Validator;

use App\Helper\SecurityHelper;
use App\Manager\UserManager;

class LoginFormValidator extends BaseValidator
{
    private $userManager;
    private $securityHelper;

    public function __construct(UserManager $userManager, SecurityHelper $securityHelper)
    {
        $this->userManager = $userManager;
        $this->securityHelper = $securityHelper;
    }

    public function validate(array $data): array
    {
        $validationRules = [
            'email' => ['type' => 'empty', 'errorMsg' => 'Veuillez saisir une adresse e-mail.', 'required' => true],
            'password' => ['type' => 'empty', 'errorMsg' => 'Veuillez entrer un mot de passe.', 'required' => true],
        ];

        $errors = $this->validateData($data, $validationRules);

        if (empty($errors)) {
            $user = $this->userManager->findOneBy(['email' => $data['email']]);

            if (!$user) {
                $errors['username'] = "Ce nom d'utilisateur n'existe pas.";
            } elseif (!password_verify($data['password'], $user->getPassword())) {
                $errors['password'] = 'Ce mot de passe est incorrect.';
            }
        }

        return $errors;
    }

    public function shouldRemember(array $data): bool
    {
        return isset($data['remember']) && 'true' === $data['remember'];
    }

    protected function validateCsrfToken($token, $errorMsg)
    {
        return [
            'valid' => $this->securityHelper->checkCsrfToken('login', $token),
            'errorMsg' => $errorMsg,
        ];
    }
}
