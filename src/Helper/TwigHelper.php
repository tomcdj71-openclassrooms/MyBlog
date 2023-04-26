<?php

declare(strict_types=1);

namespace App\Helper;

use App\Router\Route;
use App\Router\ServerRequest;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;
use Twig\TwigFunction;

class TwigHelper
{
    protected $twig;
    private $serverRequest;

    public function __construct(ServerRequest $serverRequest)
    {
        $this->serverRequest = $serverRequest;
        $loader = new FilesystemLoader(dirname(__DIR__).'/View');

        $this->twig = new Environment($loader, [
            'cache' => false,
            'debug' => true,
            'strict_variables' => true,
        ]);
        /*
         * Add custom functions to Twig.
         * The above addFunction() refers to the public functions below.
         */
        $this->twig->addFunction(new TwigFunction('asset', [$this, 'asset']));
        $this->twig->addFunction(new TwigFunction('path', [$this, 'path']));
        $this->twig->addFunction(new TwigFunction('paginate', [$this, 'paginate']));
        // Required for the dump() function
        // VSCode don't like this line but it's working
        $this->twig->addExtension(new \Twig\Extension\DebugExtension());
    }

    /**
     * This function takes a path relative to the templates directory.
     *
     * @param mixed $template
     * @param mixed $data
     */
    public function render($template, $data = [])
    {
        echo $this->twig->render($template, $data);
    }

    /**
     * Generates a URL for a given route.
     *
     * @param string $name   The name of the route
     * @param array  $params An array of parameters to pass to the route
     *
     * @return string The generated URL
     */
    public function path($name, $params = [])
    {
        $route = new Route();
        $routes = $route->getRoutes();

        if (isset($routes[$name])) {
            $path = $routes[$name][0];

            return preg_replace_callback('/{([^}]*)}/', function ($match) use ($params) {
                $param = $match[1];
                if (isset($params[$param])) {
                    return $params[$param];
                }

                return $match[0];
            }, $path);
        }

        throw new \Exception(sprintf('Route "%s" introuvable', $name));
    }

    /**
     * Generates an asset URL.
     *
     * @param mixed $asset
     *
     * @return string The generated URL
     */
    public function asset($asset)
    {
        $protocol = $this->serverRequest->isSecure() ? 'https' : 'http';
        $host = $this->serverRequest->get('HTTP_HOST');
        $baseURL = "{$protocol}://{$host}";

        return sprintf('%s/assets/%s', $baseURL, ltrim($asset, '/'));
    }
}
