<?php

declare(strict_types=1);

namespace App\Service;

use App\DependencyInjection\Container;
use App\Helper\SecurityHelper;
use App\Manager\CommentManager;
use App\Manager\UserManager;
use App\Middleware\AuthenticationMiddleware;
use App\Router\ServerRequest;
use App\Router\Session;
use App\Validator\CommentFormValidator;

class CommentService extends AbstractService
{
    protected SecurityHelper $securityHelper;
    protected ServerRequest $serverRequest;
    protected AuthenticationMiddleware $authMiddleware;
    protected CommentManager $commentManager;
    protected CsrfTokenService $csrfTokenService;
    protected UserManager $userManager;
    protected Session $session;

    public function __construct(Container $container)
    {
        $container->injectProperties($this);
        $this->serverRequest = $container->get(ServerRequest::class);
        $this->securityHelper = $container->get(SecurityHelper::class);
        $this->authMiddleware = $container->get(AuthenticationMiddleware::class);
        $this->commentManager = $container->get(CommentManager::class);
        $this->csrfTokenService = $container->get(CsrfTokenService::class);
        $this->userManager = $container->get(UserManager::class);
        $this->session = $container->get(Session::class);
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
        $message = $response['valid'] ? $this->createComment($postData) : null;
        $errors = $response['valid'] ? null : $response['errors'];

        return [$errors, $message];
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
        $this->authMiddleware->isAdmin() ? $isEnabled = true : $isEnabled = false;

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
