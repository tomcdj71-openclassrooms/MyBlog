<?php

declare(strict_types=1);

namespace App\Controller;

use App\DependencyInjection\Container;

class ErrorController extends AbstractController
{
    private $data;

    public function __construct(Container $container)
    {
        parent::__construct($container);
        $container->injectProperties($this);
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

        switch ($statusCode) {
            case 404:
                $this->data['title'] = 'Erreur 404 - Page non trouvée';
                $this->data['message_title'] = '404';
                $this->data['message'] = 'Page non trouvée';
                $this->data['explanations'] = 'La page que vous recherchez a peut-être été supprimée, son nom a changé ou est temporairement indisponible.';
                $this->data['status_code'] = $statusCode;

                break;

            case 403:
                $this->data['title'] = 'Erreur 403 - Interdit';
                $this->data['message_title'] = '403';
                $this->data['message'] = 'Interdit';
                $this->data['explanations'] = "Vous n'avez pas la permission d'accéder à cette page.";
                $this->data['status_code'] = $statusCode;

                break;

            case 500:
                $this->data['title'] = 'Erreur 500 - Erreur interne du serveur';
                $this->data['message_title'] = '500';
                $this->data['message'] = 'Erreur interne du serveur';
                $this->data['explanations'] = "Le serveur a rencontré une erreur interne ou une mauvaise configuration et n'a pas pu traiter votre demande.";
                $this->data['status_code'] = $statusCode;

                break;

            case 400:
                $this->data['title'] = 'Erreur 400 - Mauvaise demande';
                $this->data['message_title'] = '400';
                $this->data['message'] = 'Mauvaise demande';
                $this->data['explanations'] = "Le serveur n'a pas compris la requête.";
                $this->data['status_code'] = $statusCode;

                break;

            case 401:
                $this->data['title'] = 'Erreur 401 - Non autorisé';
                $this->data['message_title'] = '401';
                $this->data['message'] = 'Non autorisé';
                $this->data['explanations'] = 'Cette page nécessite une authentification.';
                $this->data['status_code'] = $statusCode;

                break;

            case 405:
                $this->data['title'] = 'Erreur 405 - Méthode Non Autorisée';
                $this->data['message_title'] = '405';
                $this->data['message'] = 'Méthode Non Autorisée';
                $this->data['explanations'] = "La méthode spécifiée dans la demande n'est pas autorisée pour la ressource identifiée par l'URI de la demande.";
                $this->data['status_code'] = $statusCode;

                break;

            default:
                $this->data['title'] = 'Erreur';
                $this->data['message_title'] = 'Erreur';
                $this->data['message'] = 'Erreur';
                $this->data['explanations'] = 'Une erreur est survenue.';
                $this->data['status_code'] = $statusCode;

                break;
        }

        echo $this->twig->render('pages/errors/error.html.twig', $this->data);
    }

    private function resetData()
    {
        $this->data = [
            'route' => 'error',
            'title' => '',
            'message_title' => '',
            'message' => '',
            'explanations' => '',
            'status_code' => '',
            'session' => $this->session,
        ];
    }
}
