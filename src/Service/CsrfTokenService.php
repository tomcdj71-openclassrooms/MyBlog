<?php

declare(strict_types=1);

namespace App\Service;

use App\Router\Session;

class CsrfTokenService
{
    private Session $session;

    public function __construct(Session $session)
    {
        $this->session = $session;
    }

    public function generateToken(string $key): string
    {
        $csrfTokens = $this->session->get('csrfTokens');
        if (isset($csrfTokens[$key])) {
            return $csrfTokens[$key];
        }
        $token = bin2hex(random_bytes(32));
        $csrfTokens[$key] = $token;
        $this->session->set('csrfTokens', $csrfTokens);

        return $token;
    }

    public function checkCsrfToken(string $key, string $token, string $errorMsg = ''): bool
    {
        $csrfTokens = $this->session->get('csrfTokens');
        $expected = $csrfTokens[$key] ?? null;
        if (null === $expected) {
            throw new \InvalidArgumentException('Pas de jeton CSRF trouvé pour cette clé.');
        }

        return hash_equals($expected, $token);
    }
}
