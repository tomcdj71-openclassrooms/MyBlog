<?php

declare(strict_types=1);

namespace App\Helper;

use Twig\Environment;
use Twig\Loader\FilesystemLoader;
use Twig\TwigFunction;

class TwigHelper
{
    protected $twig;

    public function __construct()
    {
        $loader = new FilesystemLoader(dirname(__DIR__).'/View');

        $this->twig = new Environment($loader, [
            'cache' => false,
            'debug' => true,
            'strict_variables' => true,
        ]);
        $this->twig->addFunction(new TwigFunction('asset', function ($asset) {
            $protocol = isset($_SERVER['HTTPS']) && 'off' !== $_SERVER['HTTPS'] ? 'https' : 'http';
            $host = $_SERVER['HTTP_HOST'];
            $baseURL = "{$protocol}://{$host}";

            return sprintf('%s/assets/%s', $baseURL, ltrim($asset, '/'));
        }));
        // Required for the dump() function
        // VSCode don't like this line but it's working
        $this->twig->addExtension(new \Twig\Extension\DebugExtension());
    }

    /**
     * This function takes a path relative to the assets directory.
     *
     * @param [string] $path
     */
    public function asset_url($path)
    {
        return '/assets/'.$path;
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
}
