<?php

declare(strict_types=1);

namespace App\Service;

use App\DependencyInjection\Container;
use App\Manager\CommentManager;
use App\Validator\CommentFormValidator;

class CommentService extends AbstractService
{
    private CommentManager $commentManager;

    public function __construct(Container $container)
    {
        $container->injectProperties($this);
    }

    public function handleCommentPostRequest($postObject, array $postData)
    {
        $errors = [];
        $csrfToCheck = $this->serverRequest->getPost('csrfToken');
        if (!$this->securityHelper->checkCsrfToken('comment', $csrfToCheck)) {
            $errors[] = 'Jeton CSRF invalide.';
        }
        $postData = $this->getPostData($postObject);
        $commentFV = new CommentFormValidator($this->securityHelper);
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
