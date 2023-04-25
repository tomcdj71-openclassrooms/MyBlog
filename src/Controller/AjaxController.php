<?php

namespace App\Controller;

use App\DependencyInjection\Container;
use App\Helper\SecurityHelper;
use App\Manager\CommentManager;
use App\Middleware\AuthenticationMiddleware;
use App\Service\PostService;
use Tracy\Debugger;

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
                'actions' => [
                    'voir' => '/post/'.$comment->getPost()->getSlug().'#comment-'.$comment->getId(),
                ],
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

        $userPostsArray = [];
        foreach ($userPostsData['posts'] as $post) {
            $userPostsArray[] = [
                'id' => $post->getId(),
                'title' => $post->getTitle(),
                'slug' => $post->getSlug(),
                'created_at' => $post->getCreatedAt(),
                'is_enabled' => $post->getIsEnabled(),
                'type' => 'myPosts',
                'actions' => [
                    'voir' => '/post/'.$post->getSlug(),
                    'modifier' => '/admin/post/'.$post->getId().'/edit',
                ],
            ];
        }

        header('Content-Type: application/json');
        echo json_encode($userPostsData);
    }

    public function manageAllComments()
    {
        if (!$this->authMiddleware->isUserOrAdmin()) {
            header('HTTP/1.0 403 Forbidden');
        }

        $offset = isset($_GET['offset']) ? intval($_GET['offset']) : 1;
        $limit = isset($_GET['limit']) ? intval($_GET['limit']) : 10;
        $page = intval($offset / $limit) + 1;
        $results = $this->commentManager->findAll($page, $limit);
        $comments = $results['comments'];
        $totalComments = $results['total_comments'];

        $commentsArray = [];
        foreach ($comments as $comment) {
            $commentsArray[] = [
                'id' => $comment->getId(),
                'content' => $comment->getContent(),
                'created_at' => $comment->getCreatedAt(),
                'parent_id' => $comment->getParentId(),
                'is_enabled' => $comment->getIsEnabled(),
                'post' => [
                    'id' => $comment->getPost()->getId(),
                    'title' => $comment->getPost()->getTitle(),
                    'slug' => $comment->getPost()->getSlug(),
                ],
                'user' => [
                    'username' => $comment->getAuthor()->getUsername(),
                ],
                'type' => 'allComments',
                'actions' => [
                    'voir' => '/blog/post/'.$comment->getPost()->getSlug().'#comment-'.$comment->getId(),
                    'approuver' => '/ajax/admin-toggle-comment/'.$comment->getId(),
                    'refuser' => '/ajax/admin-toggle-comment/'.$comment->getId(),
                ],
            ];
        }

        $response = [
            'rows' => $commentsArray,
            'total' => $totalComments,
        ];

        header('Content-Type: application/json');
        echo json_encode($response);
    }

    public function toggleCommentStatus(int $commentId)
    {
        if (!$this->authMiddleware->isUserOrAdmin()) {
            header('HTTP/1.0 403 Forbidden');
        }

        $comment = $this->commentManager->find($commentId);
        Debugger::barDump($comment);
        if ($comment) {
            $comment->setIsEnabled(!$comment->getIsEnabled());
            $success = $this->commentManager->updateIsEnabled($comment);
        } else {
            $success = false;
        }

        header('Content-Type: application/json');
        echo json_encode(['success' => $success]);
    }
}
