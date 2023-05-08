<?php

declare(strict_types=1);

namespace App\Controller;

use App\Helper\SecurityHelper;
use App\Helper\TwigHelper;
use App\Manager\UserManager;
use App\Model\UserModel;
use App\Router\HttpException;
use App\Router\Request;
use App\Router\ServerRequest;
use App\Router\Session;

abstract class AbstractController
{
    protected $requestParams = [];
    protected TwigHelper $twig;
    protected Session $session;
    protected ServerRequest $serverRequest;
    protected SecurityHelper $securityHelper;
    protected UserManager $userManager;
    protected Request $request;
    protected string $path;

    public function __construct(TwigHelper $twig, Session $session, ServerRequest $serverRequest, SecurityHelper $securityHelper, UserManager $userManager, Request $request)
    {
        $this->twig = $twig;
        $this->session = $session;
        $this->serverRequest = $serverRequest;
        $this->securityHelper = $securityHelper;
        $this->userManager = $userManager;
        $this->request = $request;
        $this->path = $this->serverRequest->getPath();
    }

    public function updateRequestParams(array $params): void
    {
        $this->requestParams = $params;
    }

    public function denyAccessUnlessAdmin(): void
    {
        $this->denyAccessUnless(
            fn () => $this->securityHelper->hasRole('ROLE_ADMIN'),
            "Accès refusé. Vous n'avez pas la permission d'accéder à cette page."
        );
    }

    public function denyAccessIfAuthenticated(): void
    {
        $this->denyAccessUnless(
            fn () => $this->isUserUnauthenticated(),
            'Accès refusé. Vous êtes déjà connecté.'
        );
    }

    protected function getUserWithRole(string $role = 'ROLE_USER'): ?UserModel
    {
        $user = $this->securityHelper->getUser();
        if (!$this->securityHelper->hasRole($role)) {
            return null;
        }

        return $user;
    }

    protected function getUser(): ?UserModel
    {
        return $this->securityHelper->getUser();
    }

    private function isUserUnauthenticated(): bool
    {
        return null === $this->securityHelper->getUser();
    }

    private function denyAccessUnless(callable $condition, string $message): void
    {
        if (!call_user_func($condition)) {
            throw new HttpException(403, $message);
        }
    }
}
