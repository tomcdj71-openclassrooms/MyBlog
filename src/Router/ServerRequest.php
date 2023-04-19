<?php

declare(strict_types=1);

namespace App\Router;

class ServerRequest extends Request
{
    private array $server;

    public function __construct(array $server = [])
    {
        $this->server = $server ?: $_SERVER;
    }

    public function get(string $key, $default = null)
    {
        return $this->server[$key] ?? $default;
    }

    public function has(string $key): bool
    {
        return isset($this->server[$key]);
    }

    /**
     * Must determine the HTTP method of the request.
     * Request method MUST be one of: GET, POST, PUT, DELETE, HEAD, OPTIONS.
     */
    public function getRequestMethod(): string
    {
        $method = $this->get('REQUEST_METHOD');

        if ($method && in_array($method, ['GET', 'POST', 'PUT', 'DELETE', 'HEAD', 'OPTIONS'])) {
            return $method;
        }

        throw new \Exception('Unable to determine HTTP request method.');
    }

    public function getUri(): string
    {
        return $this->get('REQUEST_URI', '/');
    }

    public function isSecure(): bool
    {
        return 'on' === $this->get('HTTPS');
    }

    public function getProtocol(): string
    {
        return $this->isSecure() ? 'https' : 'http';
    }
}
