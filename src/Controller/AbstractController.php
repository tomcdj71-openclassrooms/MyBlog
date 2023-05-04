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
use Symfony\Component\HttpFoundation\RedirectResponse;

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

    protected function authenticateWithRememberMeOption(string $role = 'ROLE_USER', bool $allowUnauthenticated = false)
    {
        $user = $this->getUserWithRole($role);
        if (null === $user) {
            try {
                $response = $this->redirectIfRememberMeTokenValid();
            } catch (\Exception $exception) {
                return null;
            }
            if ($response) {
                return $response;
            }
            if (!$this->securityHelper->getUser()) {
                if ($allowUnauthenticated) {
                    return null;
                }
                $this->session->set('referrer', $this->serverRequest->getUri());

                throw new HttpException(401, 'Vous devez être connecté pour accéder à cette page.');
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
