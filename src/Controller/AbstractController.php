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
use Symfony\Component\HttpFoundation\RedirectResponse;
use Tracy\Debugger;

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
                    $referrer = $this->session->get('referrer') ?? '/blog';
                    $this->session->remove('referrer');
                    if ($referrer !== $this->serverRequest->getUri()) {
                        return $this->request->redirectToRoute($referrer);
                    }

                    return;
                }
            }
            $this->session->set('referrer', $this->serverRequest->getUri());

            return $this->request->redirectToRoute('/login');
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
                    $referrer = $this->session->get('referrer') ?? '/blog';
                    $this->session->remove('referrer');
                    if ($referrer !== $this->serverRequest->getUri()) {
                        Debugger::barDump(new RedirectResponse($referrer));

                        return new RedirectResponse($referrer);
                    }

                    return;
                }
            }
            $this->session->set('referrer', $this->serverRequest->getUri());
            Debugger::barDump(new RedirectResponse('/login'));

            return new RedirectResponse('/login');
        }
    }

    private function authenticate(): void
    {
        $middleware = new AuthenticationMiddleware($this->securityHelper);

        $middleware();
    }
}
