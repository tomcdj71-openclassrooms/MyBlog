<?php

declare(strict_types=1);

namespace App\Controller;

use App\Helper\TwigHelper;

class HomeController extends TwigHelper
{
    /**
     * Display the home page.
     *
     * @param null $message
     */
    public static function index($message = null)
    {
        $data = [
            'title' => 'MyBlog - Portfolio',
            'message' => $message,
        ];

        $twig = new TwigHelper();

        $twig->render('pages/portfolio/index.html.twig', $data);
    }
}
