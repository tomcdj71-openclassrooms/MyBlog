<?php

declare(strict_types=1);

namespace App\Router;

use App\Helper\SessionHelper;

class Router
{
    private $url;
    private $route;
    private $param;
    private $routeArr;
    private $routes = [];

    public function __construct(string $url)
    {
        $this->url = $url;
    }

    /*
    * Handles GET requests
    *
    * @param array $paramList List of parameters
    *
    * @throws \Exception If the key of the array doesn't exist or the function is not found
    */
    public function get(array $paramList = []): void
    {
        // Checks if the given key exists in the array
        if (!isset($paramList[1]) || !isset($paramList[2])) {
            throw new \Exception("Get: The key of the array doesn't exist");
        }

        $class = $paramList[1];
        $paramMethod = $paramList[2];
        $methodArr = get_class_methods($class);

        if (in_array($paramMethod, $methodArr)) {
            call_user_func([$class, $paramMethod], $this->param);
        } else {
            throw new \Exception('Function not found');
        }
    }

    /*
    * Handles POST requests
    *
    * @param array $paramList List of parameters
    * @param array $data      POST data
    *
    * @throws \Exception If the key of the array doesn't exist or the function is not found
    */
    public function post(array $paramList = [], array $data = []): void
    {
        if (!isset($paramList[1]) || !isset($paramList[2])) {
            throw new \Exception("Post: The key of the array doesn't exist");
        }

        $class = $paramList[1];
        $paramMethod = $paramList[2];
        $methodArr = get_class_methods($class);
        $data = $_POST;
        $id = $this->param;

        if (in_array($paramMethod, $methodArr)) {
            call_user_func([$class, $paramMethod], $data, $id);
        } else {
            throw new \Exception('Function not found');
        }
    }

    /*
    * Runs the router
    *
    * @throws \Exception If no routes matches
    */
    public function run(): void
    {
        $routeItem = new Route();
        $sessionHelper = new SessionHelper();

        $sessionHelper->startSession();

        $this->routes = $routeItem->getRoutes();

        if (empty($this->routes)) {
            throw new RouterException('No routes matches');
        }

        if ($this->match()) {
            $this->call();
        } else {
            throw new RouterException('No routes matches');
        }
    }

    /*
    * Matches the URL with the available routes
    *
    * @return bool Whether there is a match or not
    */
    public function match(): bool
    {
        $urlPath = rtrim($this->url, '/');
        $explodeUrl = explode('/', $urlPath);

        foreach ($this->routes as $routeArr) {
            $route = $routeArr[0];
            $explodeRoute = explode('/', $route);

            if (count($explodeUrl) === count($explodeRoute)) {
                $match = true;
                $this->param = [];
                foreach ($explodeUrl as $key => $urlPart) {
                    if (preg_match('/^{.*}$/', $explodeRoute[$key])) {
                        $this->param[] = $urlPart;
                    } elseif ($urlPart !== $explodeRoute[$key]) {
                        $match = false;

                        break;
                    }
                }

                if ($match) {
                    $this->route = $route;
                    $this->routeArr = $routeArr;

                    return true;
                }
            }
        }

        return false;
    }

    // Calls the controller and the method
    public function call(): void
    {
        $controller = $_SERVER['REQUEST_URI'];
        $method = $_SERVER['REQUEST_METHOD'];

        if ('GET' === $method) {
            $this->get($this->routeArr);
        } elseif ('POST' === $method) {
            $this->post($this->routeArr);
        }
    }
}
