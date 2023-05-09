<?php

declare(strict_types=1);

namespace App\Controller;

use App\Helper\SecurityHelper;
use App\Helper\TwigHelper;
use App\Manager\UserManager;
use App\Router\Request;
use App\Router\ServerRequest;
use App\Router\Session;

class ErrorController extends AbstractController
{
    protected UserManager $userManager;
    private $data;

    public function __construct(
        TwigHelper $twig,
        Session $session,
        ServerRequest $serverRequest,
        SecurityHelper $securityHelper,
        UserManager $userManager,
        Request $request,
    ) {
        parent::__construct($twig, $session, $serverRequest, $securityHelper, $userManager, $request);
    }

    /**
     * Display the error page.
     *
     * @param int statusCode
     */
    public function errorPage(int $statusCode)
    {
        http_response_code($statusCode);
        $this->resetData();
        $message = $this->twig->getHttpStatusCodeMessage($statusCode);
        $this->data = [
            'title' => "Erreur {$statusCode} - {$message}",
            'message_title' => $statusCode,
            'message' => $message,
            'explanations' => 'Une erreur est survenue.',
            'status_code' => $statusCode,
            'session' => $this->session,
        ];

        return $this->twig->render('pages/errors/error.html.twig', $this->data);
    }

    private function resetData()
    {
        $this->data = [
            'title' => '',
            'message_title' => '',
            'message' => '',
            'explanations' => '',
            'status_code' => '',
            'session' => $this->session,
        ];
    }
}
