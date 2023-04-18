<?php

declare(strict_types=1);

namespace App\Router;

use App\DependencyInjection\Container;

class Router
{
    private $url;
    private $container;
    private $routes;

    public function __construct(string $url, Container $container)
    {
        $this->url = $url;
        $this->container = $container;

        $route = new Route();
        $this->routes = $route->getRoutes();
    }

    public function run()
    {
        $parsedUrl = $this->parseUrl($this->url);
        $matchedRoute = $this->matchRoute($parsedUrl['path']);

        if (!$matchedRoute) {
            throw new RouterException('No matching route found');
        }

        $controllerClass = $matchedRoute[1];
        $controllerMethod = $matchedRoute[2];
        $controller = $this->container->get($controllerClass);
        $params = $parsedUrl['params'];

        call_user_func_array([$controller, $controllerMethod], $matchedRoute['params']);
    }

    public function parseUrl(string $url): array
    {
        $urlComponents = parse_url($url);
        $urlPath = $urlComponents['path'];
        $urlPath = trim($urlPath, '/');
        $urlPath = explode('/', $urlPath);

        $controllerName = ucfirst($urlPath[0] ?? 'Blog');
        $methodName = $urlPath[1] ?? 'index';
        $params = array_slice($urlPath, 2);

        return [
            'path' => '/'.implode('/', $urlPath),
            'controller' => $controllerName,
            'method' => $methodName,
            'params' => $params,
        ];
    }

    private function matchRoute(string $path): ?array
    {
        foreach ($this->routes as $route) {
            $pattern = '@^'.preg_replace('@\\\{[^/]+@', '([^/]+)', preg_quote($route[0], '@')).'$@D';
            if (preg_match($pattern, $path, $matches)) {
                array_shift($matches);
                $route['params'] = $matches;

                return $route;
            }
        }

        return null;
    }
}
