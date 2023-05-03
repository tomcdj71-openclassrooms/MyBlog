<?php

declare(strict_types=1);

namespace App\Controller;

use App\Helper\SecurityHelper;
use App\Helper\TwigHelper;
use App\Manager\UserManager;
use App\Router\Request;
use App\Router\ServerRequest;
use App\Router\Session;

class AdminController extends AbstractController
{
    public function __construct(
        TwigHelper $twig,
        Session $session,
        ServerRequest $serverRequest,
        SecurityHelper $securityHelper,
        UserManager $userManager,
        Request $request,
    ) {
        parent::__construct($twig, $session, $serverRequest, $securityHelper, $userManager, $request);
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
