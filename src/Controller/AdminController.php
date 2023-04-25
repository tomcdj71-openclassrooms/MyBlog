<?php

declare(strict_types=1);

namespace App\Controller;

use App\DependencyInjection\Container;
use App\Helper\SecurityHelper;
use App\Helper\TwigHelper;
use App\Manager\CategoryManager;
use App\Manager\CommentManager;
use App\Manager\PostManager;
use App\Manager\TagManager;
use App\Manager\UserManager;
use App\Middleware\AuthenticationMiddleware;
use App\Router\ServerRequest;

class AdminController
{
    protected TwigHelper $twig;
    private UserManager $userManager;
    private SecurityHelper $securityHelper;
    private AuthenticationMiddleware $authMiddleware;
    private ServerRequest $serverRequest;
    private CommentManager $commentManager;
    private PostManager $postManager;
    private TagManager $tagManager;
    private CategoryManager $categoryManager;

    public function __construct(Container $container)
    {
        $container->injectProperties($this);
    }

    public function index($message = null)
    {
        $this->authenticate();
        if (!$this->authMiddleware->isUser()) {
            header('Location: /');
        }

        $currentUser = $this->securityHelper->getUser();

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

        $posts = $this->postManager->findAll();
        $categories = $this->categoryManager->findAll();
        $tags = $this->tagManager->findAll();

        return $this->twig->render('pages/admin/pages/index.html.twig', [
            'title' => 'MyBlog - Admin Dashboard',
            'route' => 'admin_index',
            'users' => $usersData,
            'posts' => $posts,
            'categories' => $categories,
            'tags' => $tags,
            'loggedUser' => $currentUser,
            'message' => $message,
        ]);
    }

    public function categories($message = null)
    {
        $this->authenticate();
        if (!$this->authMiddleware->isUser()) {
            header('Location: /');
        }

        $currentUser = $this->securityHelper->getUser();

        $categories = $this->categoryManager->findAll();

        return $this->twig->render('pages/admin/pages/category_admin.html.twig', [
            'title' => 'MyBlog - Admin Categories',
            'route' => 'admin_categories',
            'categories' => $categories,
            'loggedUser' => $currentUser,
            'message' => $message,
        ]);
    }

    public function comments($message = null)
    {
        $this->authenticate();
        if (!$this->authMiddleware->isUser()) {
            header('Location: /');
        }
        $results = $this->commentManager->findAll(1, 10);

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
        $this->authenticate();
        if (!$this->authMiddleware->isUser()) {
            header('Location: /');
        }

        $currentUser = $this->securityHelper->getUser();

        $posts = $this->postManager->findAll();

        return $this->twig->render('pages/admin/pages/post_admin.html.twig', [
            'title' => 'MyBlog - Admin Dashboard',
            'route' => 'admin_posts',
            'posts' => $posts,
            'loggedUser' => $currentUser,
            'message' => $message,
        ]);
    }

    public function tags($message = null)
    {
        $this->authenticate();
        if (!$this->authMiddleware->isUser()) {
            header('Location: /');
        }

        $currentUser = $this->securityHelper->getUser();

        $tags = $this->tagManager->findAll();

        return $this->twig->render('pages/admin/pages/tag_admin.html.twig', [
            'title' => 'MyBlog - Admin Dashboard',
            'route' => 'admin_tags',
            'tags' => $tags,
            'loggedUser' => $currentUser,
            'message' => $message,
        ]);
    }

    public function users($message = null)
    {
        $this->authenticate();
        if (!$this->authMiddleware->isUser()) {
            header('Location: /');
        }

        $currentUser = $this->securityHelper->getUser();

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
            'loggedUser' => $currentUser,
            'message' => $message,
        ]);
    }

    private function authenticate(): void
    {
        $middleware = new AuthenticationMiddleware($this->securityHelper);

        $middleware();
    }
}
