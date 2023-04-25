<?php

declare(strict_types=1);

namespace App\Controller;

use App\Config\Configuration;
use App\DependencyInjection\Container;
use App\Helper\TwigHelper;
use App\Router\ServerRequest;
use App\Router\Session;
use App\Service\MailerService;

class HomeController
{
    protected TwigHelper $twig;
    private Session $session;
    private ServerRequest $serverRequest;
    private MailerService $mailer;
    private Configuration $configuration;

    public function __construct(Container $container, MailerService $mailer, Configuration $configuration)
    {
        $this->mailer = $mailer;
        $this->configuration = $configuration;
    }

    /**
     * Display the home page.
     *
     * @param null $message
     */
    public function index($message = null)
    {
        if ('POST' == $this->serverRequest->getRequestMethod()) {
            $postData = [
                'name' => filter_input(INPUT_POST, 'name', FILTER_SANITIZE_SPECIAL_CHARS),
                'email' => filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL),
                'subject' => filter_input(INPUT_POST, 'subject', FILTER_SANITIZE_SPECIAL_CHARS),
                'message' => filter_input(INPUT_POST, 'message', FILTER_SANITIZE_SPECIAL_CHARS),
            ];
            $mailerConfig = $this->configuration->get('mailer');

            try {
                $this->mailer->send(
                    $mailerConfig['from_email'],
                    $postData['email'],
                    $postData['subject'],
                    $postData['message']
                );
            } catch (\Exception $e) {
                $message = 'An error occurred while sending your message. Please try again later.';
            }
            $message = 'Your message has been sent successfully.';
        }
        $data = [
            'title' => 'MyBlog - Portfolio',
            'message' => $message,
            'route' => 'portfolio',
            'session' => $this->session,
        ];

        $this->twig->render('pages/portfolio/index.html.twig', $data);
    }
}
