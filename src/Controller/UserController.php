<?php

declare(strict_types=1);

namespace App\Controller;

use App\DependencyInjection\Container;
use App\Helper\SecurityHelper;
use App\Helper\StringHelper;
use App\Helper\TwigHelper;
use App\Manager\CommentManager;
use App\Manager\UserManager;
use App\Middleware\AuthenticationMiddleware;
use App\Model\UserModel;
use App\Router\Request;
use App\Router\ServerRequest;
use App\Router\Session;
use App\Service\ProfileService;
use App\Validator\LoginFormValidator;
use App\Validator\RegisterFormValidator;

class UserController
{
    protected TwigHelper $twig;
    private UserManager $userManager;
    private SecurityHelper $securityHelper;
    private AuthenticationMiddleware $authMiddleware;
    private Session $session;
    private ServerRequest $serverRequest;
    private ProfileService $profileService;
    private Request $request;
    private StringHelper $stringHelper;
    private CommentManager $commentManager;

    public function __construct(Container $container)
    {
        $container->injectProperties($this);
    }

    /*
    * Display the profile page.
    *
    * @param null $message
    */
    public function profile()
    {
        if (!$this->authMiddleware->isUserOrAdmin()) {
            return $this->request->redirectToRoute('login', ['message' => 'You must be logged in to access this page.']);
        }

        $user = $this->securityHelper->getUser();
        $errors = [];
        $message = null;

        if ('POST' == $this->serverRequest->getRequestMethod() && filter_input(INPUT_POST, 'csrf_token', FILTER_SANITIZE_SPECIAL_CHARS)) {
            list($errors, $message) = $this->profileService->handleProfilePostRequest($user);
        }

        $csrf_token = $this->securityHelper->generateCsrfToken('editProfile');
        $data = [
            'title' => 'MyBlog - Profile',
            'route' => 'profile',
            'user' => $user,
            'message' => $message,
            'errors' => $errors,
            'csrf_token' => $csrf_token,
            'session' => $this->session,
        ];

        $this->twig->render('pages/profile/profile.html.twig', $data);
    }

    /**
     * Display the login page.
     *
     * @param null $message
     */
    public function login($message = null)
    {
        try {
            if (isset($_COOKIE['remember_me_token']) && !$this->authMiddleware->isUserOrAdmin()) {
                $this->securityHelper->checkRememberMeToken($_COOKIE['remember_me_token']);
            }
        } catch (\Exception $e) {
            // If there is an issue with the remember_me_token (expired or invalid), remove it
            setcookie('remember_me_token', '', time() - 3600, '/', '', false, true);
        }
        $this->authenticate();
        if ($this->authMiddleware->isUserOrAdmin()) {
            return $this->request->redirectToRoute('blog');
        }
        if ('POST' === $_SERVER['REQUEST_METHOD']) {
            $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
            $password = filter_input(INPUT_POST, 'password', FILTER_SANITIZE_SPECIAL_CHARS);
            $remember = filter_input(INPUT_POST, 'remember', FILTER_SANITIZE_SPECIAL_CHARS);
            $remember = $remember && 'true' === $remember ? true : false;
            $postData = [
                'email' => $email,
                'password' => $password,
                'remember' => $remember,
            ];
            $loginFV = new LoginFormValidator($this->userManager);
            $errors = $loginFV->validate($postData, $postData['remember']);
            if (empty($errors)) {
                $user = $this->securityHelper->authenticate($postData);
                if ($user instanceof UserModel) {
                    if ($postData['remember']) {
                        $this->securityHelper->rememberMe($user);
                    }

                    return $this->request->redirectToRoute('profile');
                }
                $errors[] = 'Email or password is incorrect';
            }

            return $this->request->redirectToRoute('profile');
        }
        $data = [
            'title' => 'MyBlog - Connexion',
            'route' => 'login',
            'message' => $message,
        ];

        $this->twig->render('pages/security/login.html.twig', $data);
    }

    /**
     * Display the register page.
     *
     * @param null $message
     */
    public function register($message = null)
    {
        if ($this->authMiddleware->isUserOrAdmin()) {
            return $this->request->redirectToRoute('login');
        }

        if ('POST' === $_SERVER['REQUEST_METHOD']) {
            $postData = [
                'email' => filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL),
                'username' => filter_input(INPUT_POST, 'username', FILTER_SANITIZE_SPECIAL_CHARS),
                'password' => filter_input(INPUT_POST, 'password', FILTER_SANITIZE_SPECIAL_CHARS),
                'passwordConfirm' => filter_input(INPUT_POST, 'passwordConfirm', FILTER_SANITIZE_SPECIAL_CHARS),
            ];

            $registerFV = new RegisterFormValidator($this->securityHelper);
            $errors = $registerFV->validate($postData);
            $valid = $errors['valid'];
            if (true === $valid) {
                $registered = $this->securityHelper->register($postData);
                if ($registered) {
                    return $this->request->redirectToRoute('login');
                }
                $errors[] = 'Registration failed. Please try again.';
            }

            $data = [
                'title' => 'MyBlog - Creer un compte',
                'route' => 'register',
                'message' => $message,
                'session' => $this->session,
            ];

            $this->twig->render('pages/security/register.html.twig', array_merge($data, ['errors' => $errors]));
        } else {
            $data = [
                'title' => 'MyBlog - Creer un compte',
                'route' => 'register',
                'message' => $message,
                'session' => $this->session,
            ];
            $this->twig->render('pages/security/register.html.twig', $data);
        }
    }

    /**
     * Display the user profile page.
     *
     * @param mixed $username
     */
    public function userProfile(string $username)
    {
        $url = $_SERVER['REQUEST_URI'];
        $username = $this->stringHelper->getLastUrlPart($url);
        $user = $this->userManager->findOneBy(['username' => $username]);
        $data = [
            'title' => 'MyBlog - Profile',
            'route' => 'profile',
            'user' => $user,
            'session' => $this->session,
        ];
        $this->twig->render('pages/profile/profile.html.twig', $data);
    }

    /**
     * Logout the user.
     */
    public function logout()
    {
        $this->session->destroy();

        return $this->request->redirectToRoute('blog');
    }

    private function authenticate(): void
    {
        $middleware = new AuthenticationMiddleware($this->securityHelper);

        $middleware();
    }
}
