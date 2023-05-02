<?php

declare(strict_types=1);

namespace App\Controller;

use App\Config\Configuration;
use App\Service\MailerService;

class HomeController extends AbstractController
{
    private Configuration $configuration;
    private MailerService $mailerService;

    public function __construct(MailerService $mailerService, Configuration $configuration)
    {
        $this->mailerService = $mailerService;
        $this->configuration = $configuration;
    }

    /**
     * Display the home page.
     */
    public function index()
    {
        if ('POST' == $this->serverRequest->getRequestMethod()) {
            $postData = [
                'name' => filter_input(INPUT_POST, 'name', FILTER_SANITIZE_SPECIAL_CHARS),
                'email' => filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL),
                'subject' => filter_input(INPUT_POST, 'subject', FILTER_SANITIZE_SPECIAL_CHARS),
                'message' => filter_input(INPUT_POST, 'message', FILTER_SANITIZE_SPECIAL_CHARS),
            ];
            $mailerConfig = $this->configuration->get('mailer');
            $this->mailerService->sendEmail(
                $postData['email'],
                $mailerConfig['from_email'],
                'Demande de contact - MyBlog',
                $this->twig->render('emails/contact.html.twig', [
                    'form' => $postData,
                ])
            );
            $message = 'Votre message a été envoyé avec succès.';
        }
        $data = [
            'message' => $message ?? null,
        ];

        return $this->twig->render('pages/portfolio/index.html.twig', $data);
    }
}
