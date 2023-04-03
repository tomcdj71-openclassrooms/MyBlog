<?php

declare(strict_types=1);

namespace App\Controller;

use App\Helper\TwigHelper;

class ErrorController extends TwigHelper
{
    /**
     * Display the 404 error page.
     *
     * @param null $message
     */
    public static function not_found($message = null)
    {
        http_response_code(404);

        $data = [
            'title' => 'Error 404 - Page not found',
            'message' => $message,
            'route' => 'error404',
        ];

        $twig = new TwigHelper();
        $twig->render('pages/errors/error404.html.twig', $data);
    }
}
