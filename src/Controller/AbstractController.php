<?php

declare(strict_types=1);

namespace App\Controller;

use App\DependencyInjection\Container;
use App\Helper\SecurityHelper;
use App\Helper\TwigHelper;
use App\Manager\UserManager;
use App\Middleware\AuthenticationMiddleware;
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
    protected AuthenticationMiddleware $authMiddleware;
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
        $this->authMiddleware = $this->container->get(AuthenticationMiddleware::class);
        $this->path = $this->serverRequest->getPath();
    }

    public function updateRequestParams(array $params): void
    {
        $this->requestParams = $params;
    }

    public function getRequestParam(string $key = null): ?string
    {
        if (null === $key) {
            return $this->requestParams;
        }

        return $this->requestParams[$key] ?? null;
    }

    public function ensureAdminAccess(): ?UserModel
    {
        return $this->authenticate('ROLE_ADMIN', '/login');
    }

    public function ensureUserIsAuthenticated(): ?UserModel
    {
        return $this->authenticate();
    }

    public function handleRequestWithRememberMeCheck(string $role = 'ROLE_USER')
    {
        try {
            $this->authenticate($role);
        } catch (\Exception $exception) {
            $response = $this->redirectIfRememberMeTokenExists();
            if ($response) {
                return $response;
            }

            return $exception;
        }

        return null;
    }

    protected function authenticate(string $role = 'ROLE_USER'): ?UserModel
    {
        $user = $this->confirmUserHasRole($role);
        if (null === $user) {
            $this->redirectIfRememberMeTokenExists();
            if (!$this->securityHelper->getUser()) {
                $this->session->set('referrer', $this->serverRequest->getUri());

                throw new \Exception('You must be logged in to access this page');
            }
        }

        return $user;
    }

    protected function ensureUnauthenticatedUser(): void
    {
        $user = $this->securityHelper->getUser();
        if (null !== $user) {
            header('Location: /blog');
        }
    }

    private function confirmUserHasRole(string $role = 'ROLE_USER'): ?UserModel
    {
        $user = $this->securityHelper->getUser();
        if (null === $user || !$this->securityHelper->hasRole($role)) {
            return null;
        }

        return $user;
    }

    private function redirectIfRememberMeTokenExists(): ?RedirectResponse
    {
        $rememberMeToken = $this->session->getCookie('remember_me_token');
        if (!$rememberMeToken) {
            return null;
        }
        $user = $this->userManager->findOneBy(['remember_me_token' => $rememberMeToken]);
        if ($user) {
            $this->securityHelper->loginById($user->getId());
            $referrer = $this->session->get('referrer') ?? '/blog';
            $this->session->remove('referrer');
            if ($referrer !== $this->serverRequest->getUri()) {
                return new RedirectResponse($referrer);
            }
        }
    }
}
