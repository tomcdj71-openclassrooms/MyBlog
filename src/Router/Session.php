<?php

declare(strict_types=1);

namespace App\Router;

class Session
{
    public function __construct()
    {
        if (!isset($_SESSION)) {
            session_start();
        }
    }

    public function set(string $key, $value): void
    {
        $_SESSION[$key] = $value;
    }

    public function get(string $key)
    {
        return $_SESSION[$key] ?? null;
    }

    public function has(string $key): bool
    {
        return isset($_SESSION[$key]);
    }

    public function remove(string $key): void
    {
        if ($this->has($key)) {
            unset($_SESSION[$key]);
        }
    }

    public function destroy(): void
    {
        if (PHP_SESSION_NONE !== session_status()) {
            session_destroy();
        }
    }

    public function getCookie(string $key)
    {
        return $_COOKIE[$key] ?? null;
    }

    public function getUserFromSession()
    {
        return $this->get('user');
    }
}
