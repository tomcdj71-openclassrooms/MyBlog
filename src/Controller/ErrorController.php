<?php

declare(strict_types=1);

namespace App\Controller;

use App\DependencyInjection\Container;
use App\Helper\TwigHelper;
use App\Router\Session;

class ErrorController
{
    protected $twig;
    private $data;
    private $session;

    public function __construct(Container $container)
    {
        $this->twig = $container->get(TwigHelper::class);
        $this->session = $container->get(Session::class);
    }

    /**
     * Display the error page.
     *
     * @param null $message
     */
    public function error_page()
    {
        $this->resetData();
        // switch case to display the correct error page
        switch (http_response_code()) {
            case 404:
                $this->data['title'] = 'Error 404 - Page not found';
                $this->data['message_title'] = '404';
                $this->data['message'] = 'Page not found';
                $this->data['explanations'] = 'The page you are looking for might have been removed, had its name changed, or is temporarily unavailable.';

                break;

            case 403:
                $this->data['title'] = 'Error 403 - Forbidden';
                $this->data['message_title'] = '403';
                $this->data['message'] = 'Forbidden';
                $this->data['explanations'] = 'You do not have permission to access this page.';

                break;

            case 500:
                $this->data['title'] = 'Error 500 - Internal Server Error';
                $this->data['message_title'] = '500';
                $this->data['message'] = 'Internal Server Error';
                $this->data['explanations'] = 'The server encountered an internal error or misconfiguration and was unable to complete your request.';

                break;

            case 400:
                $this->data['title'] = 'Error 400 - Bad Request';
                $this->data['message_title'] = '400';
                $this->data['message'] = 'Bad Request';
                $this->data['explanations'] = 'The server did not understand the request.';

                break;

            case 401:
                $this->data['title'] = 'Error 401 - Unauthorized';
                $this->data['message_title'] = '401';
                $this->data['message'] = 'Unauthorized';
                $this->data['explanations'] = 'This page requires authentication.';

                break;

            case 405:
                $this->data['title'] = 'Error 405 - Method Not Allowed';
                $this->data['message_title'] = '405';
                $this->data['message'] = 'Method Not Allowed';
                $this->data['explanations'] = 'The method specified in the request is not allowed for the resource identified by the request URI.';

                break;

            default:
                $this->data['title'] = 'Error';
                $this->data['message_title'] = 'Error';
                $this->data['message'] = 'Error';
                $this->data['explanations'] = 'An error has occurred.';

                break;
        }

        $this->twig->render('pages/errors/error.html.twig', $this->data);
    }

    private function resetData()
    {
        $this->data = [
            'route' => 'error',
            'title' => '',
            'message_title' => '',
            'message' => '',
            'explanations' => '',
            'session' => $this->session,
        ];
    }
}
