<?php

declare(strict_types=1);

require_once __DIR__.'/../vendor/autoload.php';

use App\Controller\ErrorController;
use App\DependencyInjection\Container;
use App\DependencyInjection\Injectable;
use App\Router\Router;
use App\Router\RouterException;
use Tracy\Debugger;

Debugger::enable();

// Create the container
$container = new Container();

// Register the services
Injectable::register($container);

try {
    $router = new Router($_SERVER['REQUEST_URI'], $container);
    $router->run();
} catch (RouterException $error) {
    // Set the error code to 404 by default
    http_response_code($error->getCode() ?: 404);
    $errorController = new ErrorController($container);
    $errorController->errorPage($error->getMessage());
}
