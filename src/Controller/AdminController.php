<?php

declare(strict_types=1);

namespace App\Controller;

use App\DependencyInjection\Container;
use App\Manager\CommentManager;
use App\Manager\PostManager;
use App\Manager\TagManager;

class AdminController extends AbstractController
{
    private CommentManager $commentManager;
    private PostManager $postManager;
    private TagManager $tagManager;

    public function __construct(Container $container)
    {
        parent::__construct($container);
        $container->injectProperties($this);
    }

    public function index()
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
            'users' => $usersData,
            'posts' => $this->postManager->findAll($page, $limit),
            'user' => $this->securityHelper->getUser(),
        ]);
    }

    public function categories()
    {
        return $this->twig->render('pages/admin/pages/category_admin.html.twig', [
            'title' => 'MyBlog - Admin Categories',
            'user' => $this->securityHelper->getUser(),
        ]);
    }

    public function comments()
    {
        return $this->twig->render('pages/admin/pages/comment_admin.html.twig', [
            'title' => 'MyBlog - Admin Dashboard',
            'user' => $this->securityHelper->getUser(),
        ]);
    }

    public function posts()
    {
        return $this->twig->render('pages/admin/pages/post_admin.html.twig', [
            'title' => 'MyBlog - Admin Dashboard',
            'user' => $this->securityHelper->getUser(),
        ]);
    }

    public function tags()
    {
        return $this->twig->render('pages/admin/pages/tag_admin.html.twig', [
            'title' => 'MyBlog - Admin Dashboard',
            'user' => $this->securityHelper->getUser(),
        ]);
    }

    public function users()
    {
        return $this->twig->render('pages/admin/pages/user_admin.html.twig', [
            'title' => 'MyBlog - Admin Dashboard',
            'user' => $this->securityHelper->getUser(),
        ]);
    }
}
