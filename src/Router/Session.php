<?php

declare(strict_types=1);

namespace App\Router;

class Session
{
    public function __construct()
    {
        if (!isset($_SESSION)) {
            ini_set('session.cookie_secure', '1');
            ini_set('session.cookie_httponly', '1');
            ini_set('session.use_strict_mode', '1');
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

    public function regenerateId(): void
    {
        session_regenerate_id(true);
    }
}
