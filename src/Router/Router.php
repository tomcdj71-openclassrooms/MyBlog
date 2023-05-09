<?php

declare(strict_types=1);

namespace App\Router;

use App\Controller\BlogController;
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
        $path = rtrim($parsedUrl['path'], '/');
        $matchedRoute = $this->matchRoute($path);
        if (!$matchedRoute) {
            throw new HttpException(404, 'Pas de route trouvée pour cette URL.');
        }
        $controllerClass = $matchedRoute[1];
        $controllerMethod = $matchedRoute[2];
        $controller = $this->container->get($controllerClass);
        $this->container->injectProperties($controller);
        if ($controller instanceof BlogController) {
            $controller->updateRequestParams($matchedRoute['params']);
        }

        echo call_user_func_array([$controller, $controllerMethod], $matchedRoute['params']);
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
        parse_str($urlComponents['query'] ?? '', $queryParams);

        return [
            'path' => '/'.implode('/', $urlPath),
            'controller' => $controllerName,
            'method' => $methodName,
            'params' => $params,
            'query' => $queryParams,
        ];
    }

    private function matchRoute(string $path): ?array
    {
        foreach ($this->routes as $route) {
            $pattern = '@^'.preg_replace('@\\\{[^/]+@', '([^/]+)', preg_quote(rtrim($route[0], '/'), '@')).'/?$@D';
            if (preg_match($pattern, $path, $matches)) {
                array_shift($matches);
                $route['params'] = $matches;

                return $route;
            }
        }

        return null;
    }
}
