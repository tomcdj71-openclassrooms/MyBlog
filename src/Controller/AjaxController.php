<?php

namespace App\Controller;

use App\DependencyInjection\Container;
use App\Helper\SecurityHelper;
use App\Manager\CommentManager;
use App\Middleware\AuthenticationMiddleware;
use App\Service\PostService;

class AjaxController
{
    private CommentManager $commentManager;
    private SecurityHelper $securityHelper;
    private AuthenticationMiddleware $authMiddleware;
    private PostService $postService;

    public function __construct(Container $container)
    {
        $container->injectProperties($this);
    }

    public function myComments()
    {
        if (!$this->authMiddleware->isUserOrAdmin()) {
            header('HTTP/1.0 403 Forbidden');
        }

        $user = $this->securityHelper->getUser();

        $offset = isset($_GET['offset']) ? intval($_GET['offset']) : 1;
        $limit = isset($_GET['limit']) ? intval($_GET['limit']) : 10;
        $page = intval($offset / $limit) + 1;

        $userComments = $this->commentManager->findUserComments($user->getId(), $page, $limit);
        $totalComments = $this->commentManager->countUserComments($user->getId());

        $userCommentsArray = [];
        foreach ($userComments as $comment) {
            $userCommentsArray[] = [
                'id' => $comment->getId(),
                'content' => $comment->getContent(),
                'created_at' => $comment->getCreatedAt(),
                'parent_id' => $comment->getParentId(),
                'post' => [
                    'title' => $comment->getPost()->getTitle(),
                    'slug' => $comment->getPost()->getSlug(),
                ],
                'type' => 'myComments',
            ];
        }

        $response = [
            'rows' => $userCommentsArray,
            'total' => $totalComments,
        ];

        header('Content-Type: application/json');
        echo json_encode($response);
    }

    public function myPosts()
    {
        $userPostsData = $this->postService->getUserPostsData();

        header('Content-Type: application/json');
        echo json_encode($userPostsData);
    }
}
