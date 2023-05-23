<?php

declare(strict_types=1);

require_once __DIR__.'/../vendor/autoload.php';

use App\Config\Configuration;
use App\Controller\ErrorController;
use App\DependencyInjection\Container;
use App\DependencyInjection\Injectable;
use App\Router\HttpException;
use App\Router\Router;

/*
 * As it is a dev dependency, it is not available in production environment
 * To install it, run the following command:
 * composer require --dev tracy/tracy
 * Then uncomment the following line.
 */
// use Tracy\Debugger;

// Create the container
$container = new Container();

// Register the services
Injectable::register($container);

// Enable Tracy for debugging
// if ('dev' === $container->get(Configuration::class)->get('mode')) {
//    Debugger::enable();
// }

try {
    $router = new Router($_SERVER['REQUEST_URI'], $container);
    $router->run();
} catch (HttpException $httpException) {
    $errorController = $container->get(ErrorController::class);
    echo $errorController->errorPage($httpException->getStatusCode());
}
