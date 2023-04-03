<?php

declare(strict_types=1);

require_once __DIR__.'/../vendor/autoload.php';

use App\Router\Router;
use App\Router\RouterException;
use Tracy\Debugger;

Debugger::enable();

try {
    $router = new Router($_SERVER['REQUEST_URI']);
    $router->run();
} catch (RouterException $e) {
    echo 'Error Routing:'.$e;
}
