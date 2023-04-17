<?php

declare(strict_types=1);

namespace App\Controller;

use App\Config\DatabaseConnexion;
use App\Helper\SecurityHelper;
use App\Helper\StringHelper;
use App\Helper\TwigHelper;
use App\Manager\CategoryManager;
use App\Manager\CommentManager;
use App\Manager\PostManager;
use App\Manager\TagManager;
use App\Manager\UserManager;
use App\Middleware\AuthenticationMiddleware;

class AdminController extends TwigHelper
{
    protected $twig;
    private $securityHelper;
    private $db;
    private $userManager;
    private $postManager;
    private $commentManager;
    private $categoryManager;
    private $tagManager;
    private $authenticationMiddleware;
    private $stringHelper;

    public function __construct()
    {
        $db = new DatabaseConnexion();
        $this->securityHelper = new SecurityHelper();
        $this->twig = new TwigHelper();
        $this->userManager = new UserManager($db);
        $this->postManager = new PostManager($db);
        $this->commentManager = new CommentManager($db);
        $this->categoryManager = new CategoryManager($db);
        $this->tagManager = new TagManager($db);
        $this->authenticationMiddleware = new AuthenticationMiddleware($this->securityHelper);
        $this->stringHelper = new StringHelper();
    }

    public function index($message = null)
    {
        $this->authenticate();
        if (!$this->authenticationMiddleware->isUser()) {
            header('Location: /');

            exit;
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
        if (!$this->authenticationMiddleware->isUser()) {
            header('Location: /');

            exit;
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
        if (!$this->authenticationMiddleware->isUser()) {
            header('Location: /');

            exit;
        }

        $currentUser = $this->securityHelper->getUser();

        $comments = $this->commentManager->findAll();
        $commentsData = [];
        foreach ($comments as $comment) {
            $commentsData[] = [
                'id' => $comment->getId(),
                'content' => $comment->getContent(),
                'createdAt' => $comment->getCreatedAt(),
                'post' => $comment->getPostId(),
                'author' => $comment->getAuthor(),
            ];
        }

        return $this->twig->render('pages/admin/pages/comment_admin.html.twig', [
            'title' => 'MyBlog - Admin Dashboard',
            'route' => 'admin_comments',
            'comments' => $commentsData,
            'loggedUser' => $currentUser,
            'message' => $message,
        ]);
    }

    public function posts($message = null)
    {
        $this->authenticate();
        if (!$this->authenticationMiddleware->isUser()) {
            header('Location: /');

            exit;
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
        if (!$this->authenticationMiddleware->isUser()) {
            header('Location: /');

            exit;
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
        if (!$this->authenticationMiddleware->isUser()) {
            header('Location: /');

            exit;
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
