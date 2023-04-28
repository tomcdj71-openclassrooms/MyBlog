<?php

declare(strict_types=1);

namespace App\Controller;

use App\DependencyInjection\Container;
use App\Helper\StringHelper;
use App\Manager\UserManager;
use App\Service\PostService;
use App\Service\ProfileService;
use App\Validator\LoginFormValidator;
use App\Validator\RegisterFormValidator;

class UserController extends AbstractController
{
    protected UserManager $userManager;
    private ProfileService $profileService;
    private StringHelper $stringHelper;
    private PostService $postService;
    private LoginFormValidator $loginFV;

    public function __construct(Container $container)
    {
        parent::__construct($container);
        $container->injectProperties($this);
        $this->loginFV = new LoginFormValidator($this->userManager, $this->securityHelper);
    }

    /*
    * Display the profile page.
    *
    * @param null $message
    */
    public function profile()
    {
        if (!$this->authMiddleware->isUserOrAdmin()) {
            return $this->request->redirectToRoute('login', ['message' => 'Vous devez être connecté pour accéder à cette page.']);
        }
        $user = $this->securityHelper->getUser();
        $errors = [];
        $message = null;
        if ('POST' == $this->serverRequest->getRequestMethod() && filter_input(INPUT_POST, 'csrfToken', FILTER_SANITIZE_SPECIAL_CHARS)) {
            list($errors, $message) = $this->profileService->handleProfilePostRequest($user);
        }
        $csrfToken = $this->securityHelper->generateCsrfToken('editProfile');
        $userPostsData = $this->postService->getUserPostsData();
        $hasPost = ($userPostsData['total'] > 0) ? true : false;
        $data = [
            'title' => 'MyBlog - Profile',
            'route' => 'profile',
            'user' => $user,
            'message' => $message,
            'errors' => $errors,
            'csrfToken' => $csrfToken,
            'session' => $this->session,
            'hasPost' => $hasPost,
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
            if ($this->session->getCookie('remember_me_token') && !$this->authMiddleware->isUserOrAdmin()) {
                $this->securityHelper->checkRememberMeToken($this->session->getCookie('remember_me_token'));
            }
        } catch (\Exception $e) {
            // If there is an issue with the remember_me_token (expired or invalid), remove it
            setcookie('remember_me_token', '', time() - 3600, '/', '', false, true);
        }

        if ($this->authMiddleware->isUserOrAdmin()) {
            return $this->request->redirectToRoute('blog');
        }

        $errors = [];

        if ('POST' === $this->serverRequest->getRequestMethod()) {
            $postData = [
                'email' => filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL),
                'password' => filter_input(INPUT_POST, 'password', FILTER_SANITIZE_SPECIAL_CHARS),
                'remember' => filter_input(INPUT_POST, 'remember', FILTER_SANITIZE_SPECIAL_CHARS),
                'csrfToken' => filter_input(INPUT_POST, 'csrfToken', FILTER_SANITIZE_SPECIAL_CHARS),
            ];
            $validationResult = $this->loginFV->validate($postData);
            $errors = $validationResult['errors'];
            if ($validationResult['valid']) {
                $login = $this->securityHelper->login($postData, $this->loginFV->shouldRemember($postData));
                if ($login) {
                    return $this->request->redirectToRoute('blog');
                }
                $errors = ['email' => 'Email ou mot de passe incorrect.'];
            }
        }

        $csrfToken = $this->securityHelper->generateCsrfToken('login');
        $data = [
            'title' => 'MyBlog - Connexion',
            'route' => 'login',
            'message' => $message,
            'session' => $this->session,
            'csrfToken' => $csrfToken,
            'errors' => $errors,
        ];
        $this->twig->render('pages/security/login.html.twig', array_merge($data, ['errors' => $errors]));
    }

    /**
     * Display the register page.
     *
     * @param null $message
     */
    public function register($message = null)
    {
        try {
            if ($this->session->getCookie('remember_me_token') && !$this->authMiddleware->isUserOrAdmin()) {
                $this->securityHelper->checkRememberMeToken($this->session->getCookie('remember_me_token'));
            }
        } catch (\Exception $e) {
            // If there is an issue with the remember_me_token (expired or invalid), remove it
            setcookie('remember_me_token', '', time() - 3600, '/', '', false, true);
        }
        if ($this->authMiddleware->isUserOrAdmin()) {
            return $this->request->redirectToRoute('blog');
        }
        $errors = [];
        $csrfToken = $this->securityHelper->generateCsrfToken('register');
        if ('POST' === $this->serverRequest->getRequestMethod()) {
            $postData = [
                'email' => filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL),
                'username' => filter_input(INPUT_POST, 'username', FILTER_SANITIZE_SPECIAL_CHARS),
                'password' => filter_input(INPUT_POST, 'password', FILTER_SANITIZE_SPECIAL_CHARS),
                'passwordConfirm' => filter_input(INPUT_POST, 'passwordConfirm', FILTER_SANITIZE_SPECIAL_CHARS),
                'csrfToken' => $csrfToken,
            ];
            $registerFV = new RegisterFormValidator($this->securityHelper);
            $validationResult = $registerFV->validate($postData);
            if ($validationResult['valid']) {
                $registered = $this->securityHelper->register($postData);
                if ($registered) {
                    $csrfToken = $this->securityHelper->generateCsrfToken('register');

                    return $this->request->redirectToRoute('login');
                }
                $errors[] = "Échec de l'enregistrement. Veuillez réessayer.";
            }
            $errors = array_merge($errors, $validationResult['errors']);
        }
        $data = [
            'title' => 'MyBlog - Connexion',
            'route' => 'login',
            'message' => $message,
            'session' => $this->session,
            'csrfToken' => $csrfToken,
        ];
        $this->twig->render('pages/security/register.html.twig', array_merge($data, ['errors' => $errors]));
    }

    /**
     * Display the user profile page.
     *
     * @param mixed $username
     */
    public function userProfile(string $username)
    {
        $url = $this->serverRequest->getUri();
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
        header('Location: /blog');

        return $this->request->redirectToRoute('blog');
    }
}
