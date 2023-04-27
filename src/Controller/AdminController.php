<?php

declare(strict_types=1);

namespace App\Controller;

use App\DependencyInjection\Container;
use App\Manager\CategoryManager;
use App\Manager\CommentManager;
use App\Manager\PostManager;
use App\Manager\TagManager;

class AdminController extends AbstractController
{
    private CommentManager $commentManager;
    private PostManager $postManager;
    private TagManager $tagManager;
    private CategoryManager $categoryManager;

    public function __construct(Container $container)
    {
        parent::__construct($container);
        $container->injectProperties($this);
    }

    public function index($message = null)
    {
        $users = $this->userManager->findAll();
        $usersData = [];
        foreach ($users as $user) {
            $usersData[] = [
                'id' => $user->getId(),
                'username' => $user->getUsername(),
                'email' => $user->getEmail(),
                'role' => $user->getRole(),
                'createdAt' => $user->getCreatedAt(),
            ];
        }
        $offset = $this->serverRequest->getQuery('offset', 1);
        $limit = $this->serverRequest->getQuery('limit', 10);
        $page = intval($offset / $limit) + 1;

        return $this->twig->render('pages/admin/pages/index.html.twig', [
            'title' => 'MyBlog - Admin Dashboard',
            'route' => 'admin_index',
            'users' => $usersData,
            'posts' => $this->postManager->findAll($page, $limit),
            'categories' => $this->categoryManager->findAll(),
            'tags' => $this->tagManager->findAll(),
            'user' => $this->securityHelper->getUser(),
            'message' => $message,
        ]);
    }

    public function categories($message = null)
    {
        return $this->twig->render('pages/admin/pages/category_admin.html.twig', [
            'title' => 'MyBlog - Admin Categories',
            'route' => 'admin_categories',
            'categories' => $this->categoryManager->findAll(),
            'user' => $this->securityHelper->getUser(),
            'message' => $message,
        ]);
    }

    public function comments($message = null)
    {
        $offset = $this->serverRequest->getQuery('offset', 1);
        $limit = $this->serverRequest->getQuery('limit', 10);
        $page = intval($offset / $limit) + 1;
        $results = $this->commentManager->findAll($page, $limit);

        return $this->twig->render('pages/admin/pages/comment_admin.html.twig', [
            'title' => 'MyBlog - Admin Dashboard',
            'route' => 'admin_comments',
            'comments' => $results['comments'],
            'total_comments' => $results['total_comments'],
            'user' => $this->securityHelper->getUser(),
            'message' => $message,
        ]);
    }

    public function posts($message = null)
    {
        $offset = $this->serverRequest->getQuery('offset', 1);
        $limit = $this->serverRequest->getQuery('limit', 10);
        $page = intval($offset / $limit) + 1;

        return $this->twig->render('pages/admin/pages/post_admin.html.twig', [
            'title' => 'MyBlog - Admin Dashboard',
            'route' => 'admin_posts',
            'posts' => $this->postManager->findAll($page, $limit),
            'user' => $this->securityHelper->getUser(),
            'message' => $message,
        ]);
    }

    public function tags($message = null)
    {
        return $this->twig->render('pages/admin/pages/tag_admin.html.twig', [
            'title' => 'MyBlog - Admin Dashboard',
            'route' => 'admin_tags',
            'tags' => $this->tagManager->findAll(),
            'user' => $this->securityHelper->getUser(),
            'message' => $message,
        ]);
    }

    public function users($message = null)
    {
        $offset = $this->serverRequest->getQuery('offset', 1);
        $limit = $this->serverRequest->getQuery('limit', 10);
        $page = intval($offset / $limit) + 1;
        $users = $this->userManager->findAll();
        $usersData = [];
        foreach ($users as $user) {
            $usersData[] = [
                'id' => $user->getId(),
                'username' => $user->getUsername(),
                'email' => $user->getEmail(),
                'role' => $user->getRole(),
                'createdAt' => $user->getCreatedAt(),
            ];
        }

        return $this->twig->render('pages/admin/pages/user_admin.html.twig', [
            'title' => 'MyBlog - Admin Dashboard',
            'route' => 'admin_users',
            'users' => $usersData,
            'user' => $this->securityHelper->getUser(),
            'message' => $message,
        ]);
    }
}
