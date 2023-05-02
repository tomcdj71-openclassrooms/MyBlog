<?php

declare(strict_types=1);

namespace App\Controller;

use App\DependencyInjection\Container;

class AdminController extends AbstractController
{
    public function __construct(Container $container)
    {
        parent::__construct($container);
        $container->injectProperties($this);
    }

    public function index()
    {
        return $this->twig->render('pages/admin/pages/index.html.twig', [
            'user' => $this->securityHelper->getUser(),
        ]);
    }

    public function categories()
    {
        return $this->twig->render('pages/admin/pages/category_admin.html.twig', [
            'user' => $this->securityHelper->getUser(),
        ]);
    }

    public function comments()
    {
        return $this->twig->render('pages/admin/pages/comment_admin.html.twig', [
            'user' => $this->securityHelper->getUser(),
        ]);
    }

    public function posts()
    {
        return $this->twig->render('pages/admin/pages/post_admin.html.twig', [
            'user' => $this->securityHelper->getUser(),
        ]);
    }

    public function tags()
    {
        return $this->twig->render('pages/admin/pages/tag_admin.html.twig', [
            'user' => $this->securityHelper->getUser(),
        ]);
    }

    public function users()
    {
        return $this->twig->render('pages/admin/pages/user_admin.html.twig', [
            'user' => $this->securityHelper->getUser(),
        ]);
    }
}
