<?php

declare(strict_types=1);

namespace App\Router;

class Request
{
    /**
     * This method redirects to a route based on an index.
     *
     * @param string $index The index of the route
     * @param mixed  $param Parameters to pass to the method
     *
     * @return mixed The result of the method call
     */
    public function redirectToRoute($index, $param = null)
    {
        $route = new Route();
        $routes = $route->getRoutes();

        if (isset($routes[$index])) {
            $request = $routes[$index];
            $class = $request[1];
            $method = $request[2];

            if (method_exists($class, $method)) {
                return call_user_func([$class, $method], $param);
            }
        }
    }

    /**
     * getParam.
     *
     * Get the route parameter at the specified index
     *
     * @param int $index
     *
     * @return string
     */
    public function getParam($index)
    {
        $route = new Route();
        $routes = $route->getRoutes();

        if (isset($routes[$index])) {
            $request = $routes[$index];

            return $request[0];
        }
    }

    /**
     * This method returns the method of a specific route.
     *
     * @param mixed $index
     *
     * @return string
     */
    public function getMethod($index)
    {
        $route = new Route();
        $routes = $route->getRoutes();

        if (isset($routes[$index])) {
            $request = $routes[$index];

            return $request[3];
        }
    }

    /**
     * This method returns the controller of a specific route.
     *
     * @param int $index
     *
     * @return string
     */
    public function getController($index)
    {
        $route = new Route();
        $routes = $route->getRoutes();

        if (isset($routes[$index])) {
            $request = $routes[$index];

            return $request[1];
        }
    }
}
