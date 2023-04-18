<?php

declare(strict_types=1);

require_once __DIR__.'/../vendor/autoload.php';

use App\Controller\ErrorController;
use App\DependencyInjection\Container;
use App\Helper\SecurityHelper;
use App\Helper\StringHelper;
use App\Helper\TwigHelper;
use App\Manager\CategoryManager;
use App\Manager\CommentManager;
use App\Manager\PostManager;
use App\Manager\TagManager;
use App\Manager\UserManager;
use App\Middleware\AuthenticationMiddleware;
use App\Router\Router;
use App\Router\RouterException;
use App\Router\Session;
use Tracy\Debugger;

Debugger::enable();

$container = new Container();

// Register your services here
$container->set(CategoryManager::class);
$container->set(TagManager::class);
$container->set(PostManager::class);
$container->set(UserManager::class);
$container->set(CommentManager::class);
$container->set(SecurityHelper::class);
$container->set(StringHelper::class);
$container->set(TwigHelper::class);
$container->set(AuthenticationMiddleware::class);
$container->set(SecurityHelper::class, new SecurityHelper($container->get(Session::class)));

try {
    $router = new Router($_SERVER['REQUEST_URI'], $container);
    $router->run();
} catch (RouterException $e) {
    // Set the error code to 404 by default
    http_response_code($e->getCode() ?: 404);
    $errorController = new ErrorController($container);
    $errorController->error_page($e->getMessage());
}
