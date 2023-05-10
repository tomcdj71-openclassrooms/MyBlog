<?php

declare(strict_types=1);

require_once __DIR__.'/../vendor/autoload.php';

use App\Controller\ErrorController;
use App\DependencyInjection\Container;
use App\DependencyInjection\Injectable;
use App\Router\HttpException;
use App\Router\Router;

// Create the container
$container = new Container();

// Register the services
Injectable::register($container);

try {
    $router = new Router($_SERVER['REQUEST_URI'], $container);
    $router->run();
} catch (HttpException $httpException) {
    $errorController = $container->get(ErrorController::class);
    echo $errorController->errorPage($httpException->getStatusCode());
}
