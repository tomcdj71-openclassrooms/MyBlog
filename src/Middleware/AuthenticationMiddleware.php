<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Helper\SecurityHelper;
use Tracy\Debugger;

class AuthenticationMiddleware
{
    private $securityHelper;

    public function __construct(SecurityHelper $securityHelper)
    {
        $this->securityHelper = $securityHelper;
    }

    public function __invoke(): void
    {
        $user = $this->securityHelper->getUser();

        // if user is not logged in or is not an admin or user
        if (!$user || !$this->isUserOrAdmin()) {
            header('Location: /blog');
        }
    }

    public function isAdmin(): bool
    {
        $user = $this->securityHelper->getUser();
        Debugger::barDump($user);
        if (!$user || 'ROLE_ADMIN' !== $user->getRole()) {
            return false;
        }

        return true;
    }

    public function isUser(): bool
    {
        $user = $this->securityHelper->getUser();
        if (!$user || 'ROLE_USER' !== $user->getRole()) {
            return false;
        }

        return true;
    }

    public function isUserOrAdmin(): bool
    {
        $user = $this->securityHelper->getUser();
        if (!$user || ('ROLE_USER' !== $user->getRole() && 'ROLE_ADMIN' !== $user->getRole())) {
            return false;
        }

        return true;
    }
}
