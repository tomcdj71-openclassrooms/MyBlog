<?php

declare(strict_types=1);

namespace App\Controller;

use App\Helper\TwigHelper;

class BlogController extends TwigHelper
{
    /**
     * Display the blog index page.
     *
     * @param null $message
     */
    public static function blogIndex($message = null)
    {
        $data = [
            'title' => 'MyBlog - Blog',
            'message' => $message,
            'route' => 'blog',
        ];
        $twig = new TwigHelper();
        $twig->render('pages/blog/index.html.twig', $data);
    }
}
