<?php

declare(strict_types=1);

namespace App\Controller;

use App\DependencyInjection\Container;
use App\Helper\SecurityHelper;
use App\Helper\TwigHelper;

class HomeController
{
    private $securityHelper;

    public function __construct(Container $container)
    {
        $this->securityHelper = $container->get(SecurityHelper::class);
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
            'session' => $this->securityHelper->getSession(),
        ];

        $twig = new TwigHelper();
        $twig->render('pages/portfolio/index.html.twig', $data);
    }
}
