<?php

declare(strict_types=1);

namespace App\Router;

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
        ];
    }
}
