<?php

declare(strict_types=1);

require_once __DIR__.'/../vendor/autoload.php';

use App\Controller\ErrorController;
use App\DependencyInjection\Container;
use App\DependencyInjection\Injectable;
use App\Router\HttpException;
use App\Router\Router;
use Tracy\Debugger;

// Create the container
$container = new Container();

Debugger::enable();
// Register the services
Injectable::register($container);

try {
    $router = new Router($_SERVER['REQUEST_URI'], $container);
    $router->run();
} catch (HttpException $httpException) {
    $errorController = $container->get(ErrorController::class);
    echo $errorController->errorPage($httpException->getStatusCode());
}
