<?php

namespace App\Controller;

use App\DependencyInjection\Container;
use App\Helper\SecurityHelper;
use App\Manager\CategoryManager;
use App\Manager\CommentManager;
use App\Manager\PostManager;
use App\Manager\TagManager;
use App\Manager\UserManager;
use App\Middleware\AuthenticationMiddleware;
use App\Router\ServerRequest;
use App\Service\PostService;

class AjaxController
{
    private CommentManager $commentManager;
    private SecurityHelper $securityHelper;
    private AuthenticationMiddleware $authMiddleware;
    private PostService $postService;
    private ServerRequest $request;
    private TagManager $tagManager;
    private CategoryManager $categoryManager;
    private UserManager $userManager;
    private PostManager $postManager;

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
        $offset = $this->request->getQuery('offset', 1);
        $limit = $this->request->getQuery('limit', 10);
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
        foreach ($userPostsData['rows'] as $key => $row) {
            $userPostsData['rows'][$key]['actions'] = [
                'voir' => '/blog/post/'.$userPostsData['rows'][$key]['slug'],
                'modifier' => '/admin/post/'.$userPostsData['rows'][$key]['id'].'/edit',
            ];
            $userPostsData['rows'][$key]['type'] = 'myPosts';
        }
        header('Content-Type: application/json');
        echo json_encode($userPostsData);
    }

    public function manageAllComments()
    {
        if (!$this->authMiddleware->isUserOrAdmin()) {
            header('HTTP/1.0 403 Forbidden');
        }

        $offset = $this->request->getQuery('offset', 1);
        $limit = $this->request->getQuery('limit', 10);
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
        $sucess = false;
        if ($comment) {
            $comment->setIsEnabled(!$comment->getIsEnabled());
            $success = $this->commentManager->updateIsEnabled($comment);
        }

        header('Content-Type: application/json');
        echo json_encode(['success' => $success]);
    }

    public function allTags()
    {
        $tags = $this->tagManager->findAll();
        $tagsArray = [];
        foreach ($tags as $tag) {
            $tagsArray[] = [
                'id' => $tag->getId(),
                'name' => $tag->getName(),
                'slug' => $tag->getSlug(),
                'type' => 'allTags',
                'actions' => [
                    'voir' => '/blog/tag/'.$tag->getSlug(),
                    'editer' => '/admin/tag/'.$tag->getId().'/edit',
                ],
            ];
        }
        $response = [
            'rows' => $tagsArray,
            'total' => count($tagsArray),
        ];

        header('Content-Type: application/json');
        echo json_encode($response);
    }

    public function allCategories()
    {
        $categories = $this->categoryManager->findAll();
        $categoriesArray = [];
        foreach ($categories as $category) {
            $categoriesArray[] = [
                'id' => $category->getId(),
                'name' => $category->getName(),
                'slug' => $category->getSlug(),
                'type' => 'allCategories',
                'actions' => [
                    'voir' => '/blog/category/'.$category->getSlug(),
                    'editer' => '/admin/category/'.$category->getId().'/edit',
                ],
            ];
        }
        $response = [
            'rows' => $categoriesArray,
            'total' => count($categoriesArray),
        ];

        header('Content-Type: application/json');
        echo json_encode($response);
    }

    public function allUsers()
    {
        $users = $this->userManager->findAll();
        $usersArray = [];
        foreach ($users as $user) {
            $usersArray[] = [
                'id' => $user->getId(),
                'username' => $user->getUsername(),
                'email' => $user->getEmail(),
                'roles' => $user->getRole(),
                'created_at' => $user->getCreatedAt(),
                'type' => 'allUsers',
                'actions' => [
                    'voir' => '/admin/user/'.$user->getId().'/edit',
                ],
            ];
        }
        $response = [
            'rows' => $usersArray,
            'total' => count($usersArray),
        ];
        header('Content-Type: application/json');
        echo json_encode($response);
    }

    public function allPosts()
    {
        $offset = $this->request->getQuery('offset', 1);
        $limit = $this->request->getQuery('limit', 10);
        $page = intval($offset / $limit) + 1;
        $results = $this->postManager->findAll($page, $limit);
        $posts = $results['posts'];
        $totalPosts = $results['total_posts'];
        $postsArray = [];
        $tagsArray = [];
        foreach ($posts as $post) {
            foreach ($post->getTags() as $tag) {
                $tagsArray[] = $tag->getName();
            }
            $postsArray[] = [
                'id' => $post->getId(),
                'title' => $post->getTitle(),
                'slug' => $post->getSlug(),
                'is_enabled' => $post->getIsEnabled(),
                'created_at' => $post->getCreatedAt(),
                'updated_at' => $post->getUpdatedAt(),
                'tags' => $tagsArray,
                'category' => $post->getCategory()->getName(),
                'comments' => $post->getComments(),
                'author' => $post->getAuthor()->getUsername(),
                'type' => 'allPosts',
                'actions' => [
                    'voir' => '/blog/post/'.$post->getSlug(),
                    'editer' => '/admin/post/'.$post->getId().'/edit',
                    'approuver' => '/ajax/admin-toggle-post/'.$post->getId(),
                    'refuser' => '/ajax/admin-toggle-post/'.$post->getId(),
                ],
            ];
        }
        $response = [
            'rows' => $postsArray,
            'total' => $totalPosts,
        ];
        header('Content-Type: application/json');
        echo json_encode($response);
    }
}
