<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Helper\SecurityHelper;

class AuthenticationMiddleware
{
    private $securityHelper;

    public function __construct(SecurityHelper $securityHelper)
    {
        $this->securityHelper = $securityHelper;
    }

    public function isAdmin(): bool
    {
        return $this->securityHelper->hasRole('ROLE_ADMIN');
    }

    public function isUser(): bool
    {
        return $this->securityHelper->hasRole('ROLE_USER');
    }

    public function isUserOrAdmin(): bool
    {
        return $this->isAdmin() || $this->isUser();
    }
}
