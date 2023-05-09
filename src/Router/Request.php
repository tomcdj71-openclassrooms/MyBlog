<?php

declare(strict_types=1);

namespace App\Router;

use App\DependencyInjection\Container;

class Request
{
    private $container;

    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    /**
     * This method redirects to a route based on an index.
     *
     * @param string $index  The index of the route
     * @param mixed  $param  Parameters to pass to the method
     * @param mixed  $params
     *
     * @return mixed The result of the method call
     */
    public function redirectToRoute($index, $params = [])
    {
        $route = new Route();
        $routes = $route->getRoutes();

        if (isset($routes[$index])) {
            $request = $routes[$index];
            $class = $request[1];
            $method = $request[2];

            if (method_exists($class, $method)) {
                // First, create an instance of the class using the container
                $instance = $this->container->get($class);

                // Then, call the method on the instance with the passed parameters
                return call_user_func_array([$instance, $method], $params);
            }
        }
    }

    public function generateUrl(string $routeName, array $params = []): string
    {
        $route = new Route();
        $routes = $route->getRoutes();
        if (!isset($routes[$routeName])) {
            throw new \InvalidArgumentException(sprintf('La route "%s" n\'existe pas.', $routeName));
        }
        $url = $routes[$routeName][0];
        foreach ($params as $key => $value) {
            if (is_array($value)) {
                $value = json_encode($value);
            }
            $url = str_replace('{'.$key.'}', (string) $value, $url);
        }

        return $url;
    }

    /**
     * This method redirects to a specific URL.
     */
    public function redirect(string $url, int $statusCode = 302)
    {
        header('Location: '.$url, true, $statusCode);
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
