<?php

declare(strict_types=1);

namespace App\Router;

class Session
{
    private array $sessionData;
    private array $cookieData;

    public function __construct()
    {
        if (!isset($_SESSION)) {
            ini_set('session.cookie_secure', '1');
            ini_set('session.cookie_httponly', '1');
            ini_set('session.use_strict_mode', '1');
            session_start();
        }
        $this->sessionData = &$_SESSION;
        $this->cookieData = &$_COOKIE;
    }

    public function set(string $key, $value): void
    {
        $this->sessionData[$key] = $value;
    }

    public function get(string $key)
    {
        return $this->sessionData[$key] ?? null;
    }

    public function has(string $key): bool
    {
        return isset($this->sessionData[$key]);
    }

    public function remove(string $key): void
    {
        if ($this->has($key)) {
            unset($this->sessionData[$key]);
        }
    }

    public function destroy(): void
    {
        if (PHP_SESSION_NONE !== session_status()) {
            session_destroy();
            $this->sessionData = [];
        }
    }

    public function getCookie(string $key)
    {
        return $this->cookieData[$key] ?? null;
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
