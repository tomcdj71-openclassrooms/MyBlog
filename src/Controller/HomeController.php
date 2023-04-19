<?php

declare(strict_types=1);

namespace App\Controller;

use App\DependencyInjection\Container;
use App\Helper\SecurityHelper;
use App\Helper\TwigHelper;
use App\Manager\UserManager;
use App\Middleware\AuthenticationMiddleware;
use App\Router\Request;
use App\Router\ServerRequest;
use App\Router\Session;

class HomeController
{
    protected TwigHelper $twig;
    private UserManager $userManager;
    private SecurityHelper $securityHelper;
    private AuthenticationMiddleware $authMiddleware;
    private Session $session;
    private ServerRequest $serverRequest;
    private Request $request;

    public function __construct(Container $container)
    {
        $container->injectProperties($this);
    }

    /**
     * Display the home page.
     *
     * @param null $message
     */
    public function index($message = null)
    {
        $data = [
            'title' => 'MyBlog - Portfolio',
            'message' => $message,
            'route' => 'portfolio',
            'session' => $this->session,
        ];

        $this->twig->render('pages/portfolio/index.html.twig', $data);
    }
}
