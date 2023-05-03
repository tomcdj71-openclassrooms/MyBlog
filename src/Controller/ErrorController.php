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
        $errors = [
            404 => [
                'title' => 'Erreur 404 - Page non trouvée',
                'message_title' => '404',
                'route_name' => 'home',
                'message' => 'Page non trouvée',
                'explanations' => 'La page que vous recherchez a peut-être été supprimée, son nom a changé ou est temporairement indisponible.',
            ],
            403 => [
                'title' => 'Erreur 403 - Interdit',
                'message_title' => '403',
                'message' => 'Interdit',
                'explanations' => "Vous n'avez pas la permission d'accéder à cette page.",
            ],
            500 => [
                'title' => 'Erreur 500 - Erreur interne du serveur',
                'message_title' => '500',
                'message' => 'Erreur interne du serveur',
                'explanations' => "Le serveur a rencontré une erreur interne ou une mauvaise configuration et n'a pas pu traiter votre demande.",
            ],
            400 => [
                'title' => 'Erreur 400 - Mauvaise demande',
                'message_title' => '400',
                'message' => 'Mauvaise demande',
                'explanations' => "Le serveur n'a pas compris la requête.",
            ],
            401 => [
                'title' => 'Erreur 401 - Non autorisé',
                'message_title' => '401',
                'message' => 'Non autorisé',
                'explanations' => 'Cette page nécessite une authentification.',
            ],
            405 => [
                'title' => 'Erreur 405 - Méthode Non Autorisée',
                'message_title' => '405',
                'message' => 'Méthode Non Autorisée',
                'explanations' => "La méthode spécifiée dans la demande n'est pas autorisée pour la ressource identifiée par l'URI de la demande.",
            ],
        ];
        $this->data = $errors[$statusCode] ?? [
            'title' => 'Erreur',
            'message_title' => 'Erreur',
            'message' => 'Erreur',
            'explanations' => 'Une erreur est survenue.',
        ];
        $this->data['status_code'] = $statusCode;
        $this->data['session'] = $this->session;

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
