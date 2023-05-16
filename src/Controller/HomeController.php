<?php

declare(strict_types=1);

namespace App\Controller;

use App\Config\Configuration;
use App\Service\ContactService;
use App\Service\CsrfTokenService;
use App\Service\MailerService;

class HomeController extends AbstractController
{
    private Configuration $configuration;
    private MailerService $mailerService;
    private ContactService $contactService;
    private CsrfTokenService $csrfTokenService;

    public function __construct(MailerService $mailerService, Configuration $configuration, ContactService $contactService, CsrfTokenService $csrfTokenService)
    {
        $this->mailerService = $mailerService;
        $this->configuration = $configuration;
        $this->contactService = $contactService;
        $this->csrfTokenService = $csrfTokenService;
    }

    /**
     * Display the home page.
     */
    public function index()
    {
        if ('POST' == $this->serverRequest->getRequestMethod() && filter_input(INPUT_POST, 'csrfToken', FILTER_SANITIZE_SPECIAL_CHARS)) {
            $postData = [
                'name' => $this->serverRequest->getPost('name'),
                'email' => $this->serverRequest->getPost('email'),
                'subject' => $this->serverRequest->getPost('subject'),
                'message' => $this->serverRequest->getPost('message'),
                'csrfToken' => $this->serverRequest->getPost('csrfToken'),
            ];
            list($errors, $message, $postData) = $this->contactService->handleContactPostRequest($postData);
            if ($errors) {
                $message = 'Une erreur est survenue lors de l\'envoi de votre message.';
            } else {
                $mailerConfig = $this->configuration->get('mailer');
                $mailerError = $this->mailerService->sendEmail(
                    $postData['data']['email'],
                    $mailerConfig['from_email'],
                    'Demande de contact - MyBlog',
                    $this->twig->render('emails/contact.html.twig', [
                        'data' => $postData['data'],
                    ])
                );
                if ($mailerError) {
                    unset($message);
                }
            }
        }
        $csrfToken = $this->csrfTokenService->generateToken('contact');

        return $this->twig->render('pages/portfolio/index.html.twig', [
            'user' => $this->securityHelper->getUser(),
            'message' => $message ?? null,
            'errors' => $errors ?? null,
            'response' => $response ?? null,
            'csrfToken' => $csrfToken,
            'mailerError' => $mailerError ?? null,
        ]);
    }
}
