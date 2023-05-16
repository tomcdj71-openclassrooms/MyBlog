<?php

declare(strict_types=1);

namespace App\Service;

use App\Helper\SecurityHelper;
use App\Manager\CommentManager;
use App\Manager\UserManager;
use App\Router\ServerRequest;
use App\Router\Session;
use App\Validator\ContactFormValidator;

class ContactService extends AbstractService
{
    protected CommentManager $commentManager;
    protected Session $session;
    protected CsrfTokenService $csrfTokenService;
    protected ServerRequest $serverRequest;
    protected SecurityHelper $securityHelper;
    protected UserManager $userManager;

    public function __construct(CommentManager $commentManager, Session $session, CsrfTokenService $csrfTokenService, ServerRequest $serverRequest, SecurityHelper $securityHelper, UserManager $userManager)
    {
        $this->commentManager = $commentManager;
        $this->session = $session;
        $this->csrfTokenService = $csrfTokenService;
        $this->serverRequest = $serverRequest;
        $this->securityHelper = $securityHelper;
        $this->userManager = $userManager;
    }

    public function handleContactPostRequest(array $postData)
    {
        $errors = [];
        $csrfToCheck = $this->serverRequest->getPost('csrfToken');
        if (!$this->csrfTokenService->checkCsrfToken('contact', $csrfToCheck)) {
            $errors[] = 'Jeton CSRF invalide.';
        }
        $postData = $this->getPostData($postData);
        $commentFV = new ContactFormValidator($this->userManager, $this->session, $this->csrfTokenService);
        $response = $commentFV->validate($postData);
        $response['data'] = $postData;
        $message = $response['valid'] ? 'Votre demande de contact a été prise en compte!' : 'Votre demande de contact n\'a pas pu être prise en compte.';
        $errors = !$response['valid'] ? $response['errors'] : $errors;

        return [$errors, $message, $response];
    }

    public function getPostData(array $postData)
    {
        $fields = ['email', 'name', 'subject', 'message', 'csrfToken'];

        return array_map(function ($field) {
            return $this->serverRequest->getPost($field, '');
        }, array_combine($fields, $fields));
    }
}
