<?php

declare(strict_types=1);

namespace App\Controller;

use App\DependencyInjection\Container;
use App\Helper\SecurityHelper;
use App\Helper\TwigHelper;
use App\Manager\UserManager;
use App\Middleware\AuthenticationMiddleware;
use App\Router\Request;
use App\Router\ServerRequest;
use App\Router\Session;

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
    }

    public function setRequestParams(array $params)
    {
        $this->requestParams = $params;
    }

    public function getRequestParams(string $key = null)
    {
        if (null === $key) {
            return $this->requestParams;
        }

        return $this->requestParams[$key] ?? null;
    }

    public function ensureAdmin()
    {
        $this->authenticate();

        if (!$this->authMiddleware->isAdmin()) {
            $rememberMeToken = $this->session->getCookie('remember_me_token') ?? null;
            if ($rememberMeToken) {
                $user = $this->userManager->findOneBy(['remember_me_token' => $rememberMeToken]);
                if ($user) {
                    $this->securityHelper->loginById($user->getId());
                    $referrer = $this->session->get('referrer');
                    if ($referrer) {
                        $this->session->remove('referrer');
                        header("Location: {$referrer}");

                        exit;
                    }

                    return;
                }
            }
            $this->session->set('referrer', $this->serverRequest->get('HTTP_REFERER'));
            header('Location: /login');

            exit;
        }
    }

    public function ensureAuthenticatedUser()
    {
        $this->authenticate();
        if (!$this->authMiddleware->isUserOrAdmin()) {
            $rememberMeToken = $this->session->getCookie('remember_me_token') ?? null;
            if ($rememberMeToken) {
                $user = $this->userManager->findOneBy(['remember_me_token' => $rememberMeToken]);
                if ($user) {
                    $this->securityHelper->loginById($user->getId());

                    return;
                }
            }
            $this->session->set('referrer', $this->serverRequest->getUri());
            header('Location: /login');

            exit;
        }

        $referrer = $this->session->get('referrer') ?? '/blog';
        $this->session->remove('referrer');
        header("Location: {$referrer}");

        exit;
    }

    private function authenticate(): void
    {
        $middleware = new AuthenticationMiddleware($this->securityHelper);

        $middleware();
    }
}
