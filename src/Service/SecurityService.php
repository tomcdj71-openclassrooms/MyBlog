<?php

declare(strict_types=1);

namespace App\Service;

use App\Helper\SecurityHelper;
use App\Manager\UserManager;
use App\Model\UserModel;
use App\Router\ServerRequest;
use App\Router\Session;
use App\Validator\LoginFormValidator;

class SecurityService extends AbstractService
{
    protected UserManager $userManager;
    protected CsrfTokenService $csrfTokenService;
    protected Session $session;
    protected SecurityHelper $securityHelper;
    protected ServerRequest $serverRequest;

    public function __construct(UserManager $userManager, CsrfTokenService $csrfTokenService, Session $session, SecurityHelper $securityHelper, ServerRequest $serverRequest)
    {
        $this->userManager = $userManager;
        $this->csrfTokenService = $csrfTokenService;
        $this->session = $session;
        $this->securityHelper = $securityHelper;
        $this->serverRequest = $serverRequest;
    }

    public function handleLoginPostRequest()
    {
        $errors = [];
        $csrfToCheck = $this->serverRequest->getPost('csrfToken');
        if (!$this->csrfTokenService->checkCsrfToken('login', $csrfToCheck)) {
            $errors[] = 'Jeton CSRF invalide.';
        }
        if (empty($errors)) {
            $formData = $this->getFormData();
            $loginFV = new LoginFormValidator($this->userManager, $this->session, $this->csrfTokenService);
            $response = $loginFV->validate($formData);
            $login = null;
            $message = null;
            if ($response['valid']) {
                $authentication = $this->securityHelper->authenticateUser($formData);
                if ($authentication instanceof UserModel) {
                    $login = $authentication;
                } else {
                    $errors[] = ' Utilisateur ou mot de passe incorrect.';
                    $message = 'Formulaire invalide.';
                }
            }

            return [$errors, $message, $formData, $login];
        }

        return [$errors, null, null, null];
    }

    public function getFormData()
    {
        $fields = ['email', 'password', 'csrfToken'];
        $formData = array_map(function ($field) {
            return $this->serverRequest->getPost($field, '');
        }, array_combine($fields, $fields));
        $formData['csrfToken'] = $this->serverRequest->getPost('csrfToken');

        return $formData;
    }
}
