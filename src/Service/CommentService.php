<?php

declare(strict_types=1);

namespace App\Service;

use App\DependencyInjection\Container;
use App\Helper\SecurityHelper;
use App\Manager\CommentManager;
use App\Middleware\AuthenticationMiddleware;
use App\Validator\CommentFormValidator;

class CommentService
{
    private SecurityHelper $securityHelper;
    private CommentManager $commentManager;
    private AuthenticationMiddleware $authMiddleware;

    public function __construct(Container $container)
    {
        $container->injectProperties($this);
    }

    public function handleCommentPostRequest($postObject, array $postData)
    {
        $errors = [];
        $csrf_to_check = $_POST['csrf_token'];
        if (!$this->securityHelper->checkCsrfToken('comment', $csrf_to_check)) {
            $errors[] = 'Invalid CSRF token';
        }
        $postData = $this->getPostData($postObject);
        $commentFV = new CommentFormValidator($this->securityHelper);
        $response = $commentFV->validate($postData);

        if ($response['valid']) {
            $message = $this->createComment($response['data']);
        } else {
            $errors = $response['errors'];
            $message = null;
        }

        return [$errors, $message];
    }

    public function getPostData($postObject)
    {
        $fields = ['content', 'parent_id', 'csrf_token'];
        $postData = array_map(function ($field) {
            return isset($_POST[$field]) ? htmlspecialchars($_POST[$field], ENT_QUOTES, 'UTF-8') : '';
        }, array_combine($fields, $fields));

        $postData['csrf_token'] = $_POST['csrf_token'];
        $postData['post_id'] = $postObject;
        $postData['author_id'] = $this->securityHelper->getUser();
        $postData['parent_id'] = $_POST['parentId'] ?? null;

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

        return 'Comment created successfully!';
    }
}
