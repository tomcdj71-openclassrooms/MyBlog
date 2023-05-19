<?php

declare(strict_types=1);

namespace App\Helper;

use App\Model\PostModel;
use App\Model\UserModel;
use App\Router\HttpException;
use App\Router\Route;
use App\Router\ServerRequest;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;
use Twig\TwigFunction;

class TwigHelper
{
    protected $twig;
    private $serverRequest;
    private $route;

    public function __construct(ServerRequest $serverRequest, Route $route)
    {
        $this->serverRequest = $serverRequest;
        $this->route = $route;
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
        $this->twig->addFunction(new TwigFunction('current_route', [$this, 'currentRoute']));
        $this->twig->addFunction(new TwigFunction('route_name', [$this, 'routeName']));

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
    public function render($template, $data = []): string
    {
        return $this->twig->render($template, $data);
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
        $routes = $this->route->getRoutes();
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

        throw new HttpException(404, sprintf('Route "%s" introuvable', implode(', ', array_keys($routes))));
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

    public function currentRoute($routeName)
    {
        $currentRoute = $this->serverRequest->getUri();
        $targetRoute = $this->path($routeName);

        return $currentRoute === $targetRoute;
    }

    public function routeName(?PostModel $postModel = null, ?UserModel $userModel = null): ?string
    {
        $currentUri = $this->serverRequest->getUri();
        $currentUri = rtrim($currentUri, '/');
        $currentUri = '' === $currentUri ? '/' : $currentUri;
        $routes = $this->route->getRoutes();
        $prefix = 'MyBlog - ';
        foreach ($routes as $route) {
            $routePattern = rtrim($route[0], '/');
            $routePattern = '' === $routePattern ? '/' : $routePattern;
            $pattern = '@^'.preg_replace('@\\\{[^/]+@', '([^/]+)', preg_quote($routePattern, '@')).'$@D';
            if (preg_match($pattern, $currentUri)) {
                $routeName = $route[4];
                if ('Article' === $routeName && null !== $postModel) {
                    return $prefix.$routeName.' - '.$postModel->getTitle();
                }

                return $prefix.$routeName;
            }
        }

        return null;
    }

    /**
     * Get a message based on the provided HTTP status code.
     *
     * @param int $statusCode The HTTP status code
     *
     * @return string The message associated with the status code
     */
    public function getHttpStatusCodeMessage(int $statusCode): string
    {
        $messages = [
            400 => 'Mauvaise requête',
            401 => 'Non autorisé',
            403 => 'Interdit',
            404 => 'Non trouvé',
            405 => 'Méthode non autorisée',
            408 => 'Expiration de la demande',
            500 => 'Erreur interne du serveur',
            503 => 'Service indisponible',
        ];

        return $messages[$statusCode] ?? 'Erreur Inconnue';
    }
}
