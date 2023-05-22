<?php

declare(strict_types=1);

namespace App\Service;

use App\Helper\SecurityHelper;
use App\Manager\CommentManager;
use App\Manager\UserManager;
use App\Router\ServerRequest;
use App\Router\Session;
use App\Validator\CommentFormValidator;

class CommentService extends AbstractService
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

    public function handleCommentPostRequest(array $formData)
    {
        $errors = [];
        $csrfToCheck = $this->serverRequest->getPost('csrfToken');
        if (!$this->csrfTokenService->checkCsrfToken('comment', $csrfToCheck)) {
            $errors[] = 'Jeton CSRF invalide.';
        }
        $formData = $this->getFormData($formData);
        $commentFV = new CommentFormValidator($this->userManager, $this->session, $this->csrfTokenService);
        $response = $commentFV->validate($formData);
        $comment = $response['valid'] ? $this->createComment($formData) : null;
        $message = $response['valid'] ? 'Votre commentaire a été ajouté avec succès!' : null;
        $errors = !$response['valid'] ? $response['errors'] : $errors;

        return [$errors, $message, $formData, $comment];
    }

    public function getFormData($postObject)
    {
        $fields = ['content', 'parent_id', 'csrfToken'];
        $formData = array_map(function ($field) {
            return $this->serverRequest->getPost($field, '');
        }, array_combine($fields, $fields));

        $formData['post_id'] = $postObject['post_id'] ?? null;
        $formData['author_id'] = $this->securityHelper->getUser();
        $formData['parent_id'] = $this->serverRequest->getPost('parentId') ?? null;

        return $formData;
    }

    public function createComment(array $data)
    {
        $this->securityHelper->hasRole('ROLE_ADMIN') ? $isEnabled = true : $isEnabled = false;

        $commentData = [
            'content' => $data['content'] ?? '',
            'parent_id' => isset($data['parent_id']) ? (int) $data['parent_id'] : null,
            'author_id' => $data['author_id'] ? $data['author_id']->getId() : null,
            'post_id' => $data['post_id'] ? $data['post_id']->getId() : null,
            'is_enabled' => $isEnabled,
            'created_at' => date('Y-m-d H:i:s'),
        ];
        $this->commentManager->create($commentData);

        return 'Commentaire créé avec succès!';
    }
}
