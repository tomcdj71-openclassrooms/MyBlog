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

    public function handleCommentPostRequest($postObject, array $postData)
    {
        $errors = [];
        $csrfToCheck = $this->serverRequest->getPost('csrfToken');
        if (!$this->csrfTokenService->checkCsrfToken('comment', $csrfToCheck)) {
            $errors[] = 'Jeton CSRF invalide.';
        }
        $postData = $this->getPostData($postObject);
        $commentFV = new CommentFormValidator($this->userManager, $this->session, $this->csrfTokenService);
        $response = $commentFV->validate($postData);
        $comment = $response['valid'] ? $this->createComment($postData) : null;
        $message = $response['valid'] ? 'Votre commentaire a été ajouté avec succès!' : null;
        $errors = !$response['valid'] ? $response['errors'] : $errors;

        return [$errors, $message, $postData, $comment];
    }

    public function getPostData($postObject)
    {
        $fields = ['content', 'parent_id', 'csrfToken'];
        $postData = array_map(function ($field) {
            return $this->serverRequest->getPost($field, '');
        }, array_combine($fields, $fields));

        $postData['csrfToken'] = $this->serverRequest->getPost('csrfToken');
        $postData['post_id'] = $postObject;
        $postData['author_id'] = $this->securityHelper->getUser();
        $postData['parent_id'] = $this->serverRequest->getPost('parentId') ?? null;

        return $postData;
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
