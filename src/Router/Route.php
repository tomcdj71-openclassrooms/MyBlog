<?php

declare(strict_types=1);

namespace App\Router;

use App\Controller\ErrorController;
use App\Controller\HomeController;

class Route
{
    /**
     * Define the routes.
     *
     * @var array
     */
    public function getRoutes()
    {
        return [
            'home' => ['', HomeController::class, 'index', 'GET'],
            'not_found' => ['404', ErrorController::class, 'not_found', 'GET'],
        ];
    }
}
