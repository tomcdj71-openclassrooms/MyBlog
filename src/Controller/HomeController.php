<?php

declare(strict_types=1);

namespace App\Controller;

use App\Config\Configuration;
use App\Router\Request;
use App\Service\ContactService;
use App\Service\CsrfTokenService;
use App\Service\MailerService;

class HomeController extends AbstractController
{
    protected Request $request;
    private Configuration $configuration;
    private MailerService $mailerService;
    private ContactService $contactService;
    private CsrfTokenService $csrfTokenService;

    public function __construct(MailerService $mailerService, Configuration $configuration, ContactService $contactService, CsrfTokenService $csrfTokenService, Request $request)
    {
        $this->mailerService = $mailerService;
        $this->configuration = $configuration;
        $this->contactService = $contactService;
        $this->csrfTokenService = $csrfTokenService;
        $this->request = $request;
    }

    /**
     * Display the home page.
     */
    public function index()
    {
        $flashBag = [
            'mailerError' => $this->session->get('mailerError') ? $this->session->flash('mailerError') : null,
            'success' => $this->session->get('success') ? $this->session->flash('success') : null,
        ];

        if ('POST' == $this->serverRequest->getRequestMethod() && filter_input(INPUT_POST, 'csrfToken', FILTER_SANITIZE_SPECIAL_CHARS)) {
            list($errors, $message, $formData) = $this->contactService->handleContactPostRequest();
            if ($errors) {
                $this->session->set('formData', $formData);

                return;
            }
            $mailerError = $this->mailerService->sendEmail(
                $formData['data']['email'],
                $this->configuration->get('mailer.from_email'),
                'Demande de contact - MyBlog',
                $this->twig->render('emails/contact.html.twig', [
                    'data' => $formData['data'],
                ])
            );
            $this->session->remove('formData');
            $formData = null;
            $this->session->set('success', $message);
            $this->session->set('mailerError', $mailerError);
            $url = $this->request->generateUrl('home');
            $this->request->redirect($url.'#contact', 302);
        }
        $csrfToken = $this->csrfTokenService->generateToken('contact');

        return $this->twig->render('pages/portfolio/index.html.twig', [
            'user' => $this->securityHelper->getUser(),
            'errors' => $errors ?? null,
            'csrfToken' => $csrfToken,
            'mailerError' => $mailerError ?? null,
            'flashBag' => $flashBag ?? [],
            'formData' => $formData ?? null,
        ]);
    }
}
