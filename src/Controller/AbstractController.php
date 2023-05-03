<?php

declare(strict_types=1);

namespace App\Controller;

use App\DependencyInjection\Container;
use App\Helper\SecurityHelper;
use App\Helper\TwigHelper;
use App\Manager\UserManager;
use App\Model\UserModel;
use App\Router\Request;
use App\Router\ServerRequest;
use App\Router\Session;
use Symfony\Component\HttpFoundation\RedirectResponse;

abstract class AbstractController
{
    protected Container $container;
    protected $requestParams = [];
    protected TwigHelper $twig;
    protected Session $session;
    protected ServerRequest $serverRequest;
    protected SecurityHelper $securityHelper;
    protected UserManager $userManager;
    protected Request $request;
    protected string $path;

    public function __construct(Container $container)
    {
        $this->container = $container;
        $this->container->injectProperties($this);
        $this->twig = $this->container->get(TwigHelper::class);
        $this->session = $this->container->get(Session::class);
        $this->serverRequest = $this->container->get(ServerRequest::class);
        $this->securityHelper = $this->container->get(SecurityHelper::class);
        $this->userManager = $this->container->get(UserManager::class);
        $this->request = $this->container->get(Request::class);
        $this->path = $this->serverRequest->getPath();
    }

    public function updateRequestParams(array $params): void
    {
        $this->requestParams = $params;
    }

    public function getAuthenticatedAdmin(): ?UserModel
    {
        return $this->authenticateOrThrow('ROLE_ADMIN');
    }

    protected function getRequestParam(string $key = null): ?string
    {
        if (null === $key) {
            return $this->requestParams;
        }

        return $this->requestParams[$key] ?? null;
    }

    protected function getAuthenticatedUser(): ?UserModel
    {
        return $this->authenticateOrThrow();
    }

    protected function isUserUnauthenticated(): bool
    {
        return null === $this->securityHelper->getUser();
    }

    protected function authenticateWithRememberMeOption(string $role = 'ROLE_USER')
    {
        try {
            $this->authenticateOrThrow($role);
        } catch (\Exception $exception) {
            $response = $this->redirectIfRememberMeTokenValid();
            if ($response) {
                return $response;
            }

            return $exception;
        }

        return null;
    }

    protected function authenticateOrThrow(string $role = 'ROLE_USER'): ?UserModel
    {
        $user = $this->getUserWithRole($role);
        if (null === $user) {
            $this->redirectIfRememberMeTokenValid();
            if (!$this->securityHelper->getUser()) {
                $this->session->set('referrer', $this->serverRequest->getUri());

                throw new \Exception('Vous devez être connecté pour accéder à cette page.');
            }
        }

        return $user;
    }

    protected function getUserWithRole(string $role = 'ROLE_USER'): ?UserModel
    {
        $user = $this->securityHelper->getUser();
        if (!$this->securityHelper->hasRole($role)) {
            return null;
        }

        return $user;
    }

    protected function redirectIfRememberMeTokenValid(): ?RedirectResponse
    {
        try {
            $this->securityHelper->validateAndReturnUserFromRememberMeToken();
        } catch (\Exception $exception) {
            return null;
        }
        $referrer = $this->session->get('referrer') ?? '/blog';
        $this->session->remove('referrer');
        if ($referrer !== $this->serverRequest->getUri()) {
            return new RedirectResponse($referrer);
        }

        return null;
    }
}
