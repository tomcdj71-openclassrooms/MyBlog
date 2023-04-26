<?php

declare(strict_types=1);

namespace App\Controller;

use App\DependencyInjection\Container;
use App\Helper\TwigHelper;
use App\Router\Session;

class ErreurController
{
    protected TwigHelper $twig;
    private $data;
    private Session $session;

    public function __construct(Container $container)
    {
        $container->injectProperties($this);
    }

    /**
     * Display the error page.
     *
     * @param null $message
     */
    public function errorPage()
    {
        $this->resetData();
        // switch case to display the correct error page
        switch (http_response_code()) {
            case 404:
                $this->data['title'] = 'Erreur 404 - Page non trouvée';
                $this->data['message_title'] = '404';
                $this->data['message'] = 'Page non trouvée';
                $this->data['explanations'] = 'La page que vous recherchez a peut-être été supprimée, son nom a changé ou est temporairement indisponible.';

                break;

            case 403:
                $this->data['title'] = 'Erreur 403 - Interdit';
                $this->data['message_title'] = '403';
                $this->data['message'] = 'Interdit';
                $this->data['explanations'] = "Vous n'avez pas la permission d'accéder à cette page.";

                break;

            case 500:
                $this->data['title'] = 'Erreur 500 - Erreur interne du serveur';
                $this->data['message_title'] = '500';
                $this->data['message'] = 'Erreur interne du serveur';
                $this->data['explanations'] = "Le serveur a rencontré une erreur interne ou une mauvaise configuration et n'a pas pu traiter votre demande.";

                break;

            case 400:
                $this->data['title'] = 'Erreur 400 - Mauvaise demande';
                $this->data['message_title'] = '400';
                $this->data['message'] = 'Mauvaise demande';
                $this->data['explanations'] = "Le serveur n'a pas compris la requête.";

                break;

            case 401:
                $this->data['title'] = 'Erreur 401 - Non autorisé';
                $this->data['message_title'] = '401';
                $this->data['message'] = 'Non autorisé';
                $this->data['explanations'] = 'Cette page nécessite une authentification.';

                break;

            case 405:
                $this->data['title'] = 'Erreur 405 - Méthode Non Autorisée';
                $this->data['message_title'] = '405';
                $this->data['message'] = 'Méthode Non Autorisée';
                $this->data['explanations'] = "La méthode spécifiée dans la demande n'est pas autorisée pour la ressource identifiée par l'URI de la demande.";

                break;

            default:
                $this->data['title'] = 'Erreur';
                $this->data['message_title'] = 'Erreur';
                $this->data['message'] = 'Erreur';
                $this->data['explanations'] = 'Une erreur est survenue.';

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
