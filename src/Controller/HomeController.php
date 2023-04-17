<?php

declare(strict_types=1);

namespace App\Controller;

use App\Helper\SecurityHelper;
use App\Helper\TwigHelper;

class HomeController extends TwigHelper
{
    private $securityHelper;

    public function __construct()
    {
        $this->securityHelper = new SecurityHelper();
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
