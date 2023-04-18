<?php

declare(strict_types=1);

namespace App\Controller;

use App\Config\DatabaseConnexion;
use App\Helper\ImageHelper;
use App\Helper\SecurityHelper;
use App\Helper\StringHelper;
use App\Helper\TwigHelper;
use App\Manager\UserManager;
use App\Middleware\AuthenticationMiddleware;
use App\Model\UserModel;
use App\Validator\EditProfileFormValidator;
use App\Validator\LoginFormValidator;
use App\Validator\RegisterFormValidator;
use Tracy\Debugger;

class UserController extends TwigHelper
{
    protected $userManager;
    protected $loginFormValidator;
    protected $registerFormValidator;
    protected $securityHelper;
    protected $twig;
    private $session;
    private $editProfileFormValidator;
    private $imageHelper;
    private $authenticationMiddleware;

    public function __construct()
    {
        $db = new DatabaseConnexion();
        $this->userManager = new UserManager($db);
        $this->loginFormValidator = new LoginFormValidator($this->userManager);
        $this->registerFormValidator = new RegisterFormValidator($this->userManager);
        $this->securityHelper = new SecurityHelper();
        $this->session = $this->securityHelper->getSession();
        $this->editProfileFormValidator = new EditProfileFormValidator($this->userManager);
        $this->imageHelper = new ImageHelper('uploads/avatars/', 200, 200);
        $this->authenticationMiddleware = new AuthenticationMiddleware($this->securityHelper);
        $this->twig = new TwigHelper();
    }

    /*
    * Display the profile page.
    *
    * @param null $message
    */
    public function profile($message = null)
    {
        $errors = [];
        // Get the user from the $_SESSION
        if (null === $this->securityHelper->getUser()) {
            header('Location: /login');

            exit;
        }
        $user = $this->securityHelper->getUser();
        $userId = $user->getId();
        $userManager = new UserManager(new DatabaseConnexion());
        $user = $userManager->find($userId);
        $loggedUser = null;
        if (isset($_SESSION['user'])) {
            $loggedUser = $_SESSION['user'];
        }
        if (!$user instanceof UserModel) {
            header('Location: /login');

            exit;
        }

        if ('POST' === $_SERVER['REQUEST_METHOD'] && $csrf_token = filter_input(INPUT_POST, 'csrf_token', FILTER_SANITIZE_SPECIAL_CHARS)) {
            if ($this->securityHelper->checkCsrfToken('editProfile', $csrf_token)) {
                $errors[] = 'Invalid CSRF token';
            }

            $postData = [
                'firstName' => filter_input(INPUT_POST, 'firstName', FILTER_SANITIZE_SPECIAL_CHARS) ?? '',
                'lastName' => filter_input(INPUT_POST, 'lastName', FILTER_SANITIZE_SPECIAL_CHARS) ?? '',
                'email' => filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL) ?? '',
                'username' => filter_input(INPUT_POST, 'username', FILTER_SANITIZE_SPECIAL_CHARS) ?? '',
                'bio' => filter_input(INPUT_POST, 'bio', FILTER_SANITIZE_SPECIAL_CHARS) ?? '',
                'avatar' => $_FILES['avatar'] ?? null,
                'twitter' => filter_input(INPUT_POST, 'twitter', FILTER_SANITIZE_SPECIAL_CHARS) ?? '',
                'facebook' => filter_input(INPUT_POST, 'facebook', FILTER_SANITIZE_SPECIAL_CHARS) ?? '',
                'github' => filter_input(INPUT_POST, 'github', FILTER_SANITIZE_SPECIAL_CHARS) ?? '',
                'linkedin' => filter_input(INPUT_POST, 'linkedin', FILTER_SANITIZE_SPECIAL_CHARS) ?? '',
                'csrf_token' => $csrf_token,
            ];

            if (!empty($_FILES['avatar'] || null === $_FILES['avatar'])) {
                unset($postData['avatar']);
            }

            $response = $this->editProfileFormValidator->validate($postData);
            if ($response['valid']) {
                $data = $response['data'];
                if (isset($data['avatar']) && null !== $data['avatar']) {
                    $filename = $this->imageHelper->uploadImage($data['avatar'], 200, 200);
                    if (0 === strpos($filename, 'Error')) {
                        throw new \RuntimeException($filename);
                    }
                    $filename = explode('.', $filename)[0];
                    $data['avatar'] = $filename;
                    $user->setAvatar($filename);
                }
                // Update other user information
                $user->setFirstName($data['firstName']);
                $user->setLastName($data['lastName']);
                $user->setEmail($data['email']);
                $user->setBio($data['bio']);
                $user->setTwitter($data['twitter']);
                $user->setFacebook($data['facebook']);
                $user->setGithub($data['github']);
                $user->setLinkedin($data['linkedin']);
                if ($userManager->updateProfile($user, $data)) {
                    $user = $userManager->find($userId);
                    $message = 'Your profile has been updated successfully!';
                    // generate new CSRF token to prevent multiple submissions
                    $csrf_token = $this->securityHelper->generateCsrfToken('editProfile');
                    $data = [
                        'title' => 'MyBlog - Profile',
                        'route' => 'profile',
                        'user' => $user,
                        'message' => $message,
                        'errors' => $errors,
                        'loggedUser' => $loggedUser,
                        'csrf_token' => $csrf_token,
                    ];

                    return $this->profile($message, $data, $csrf_token);
                }
                $errors = $response['errors'];
            } else {
                $errors = $response['errors'];
            }
        } else {
            $errors = [];
        }

        $csrf_token = $this->securityHelper->generateCsrfToken('editProfile');
        $user = $this->userManager->find($userId);
        $data = [
            'title' => 'MyBlog - Profile',
            'route' => 'profile',
            'user' => $user,
            'message' => $message,
            'errors' => $errors,
            'loggedUser' => $loggedUser,
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
            if (isset($_COOKIE['remember_me_token']) && !$this->securityHelper->isAuthenticated()) {
                $this->securityHelper->checkRememberMeToken($_COOKIE['remember_me_token']);
            }
        } catch (\Exception $e) {
            // If there is an issue with the remember_me_token (expired or invalid), remove it
            setcookie('remember_me_token', '', time() - 3600, '/', '', false, true);
        }
        $this->authenticate();
        if ($this->authenticationMiddleware->isUserOrAdmin()) {
            header('Location: /blog');

            exit;
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

            $errors = $this->loginFormValidator->validate($postData, $postData['remember']);

            if (empty($errors)) {
                $user = $this->securityHelper->authenticate($postData);

                if ($user instanceof UserModel) {
                    if ($postData['remember']) {
                        $this->securityHelper->rememberMe($user);
                    }

                    header('Location: /profile');

                    exit;
                }

                $errors[] = 'Email or password is incorrect';
            }

            header('Location: /profile');
        } else {
            $data = [
                'title' => 'MyBlog - Connexion',
                'route' => 'login',
                'message' => $message,
            ];

            $this->twig->render('pages/security/login.html.twig', $data);
        }
    }

    /**
     * Display the register page.
     *
     * @param null $message
     */
    public function register($message = null)
    {
        if ($this->authenticationMiddleware->isUserOrAdmin()) {
            header('Location: /blog');

            exit;
        }

        if ('POST' === $_SERVER['REQUEST_METHOD']) {
            $postData = [
                'email' => filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL),
                'username' => filter_input(INPUT_POST, 'username', FILTER_SANITIZE_SPECIAL_CHARS),
                'password' => filter_input(INPUT_POST, 'password', FILTER_SANITIZE_SPECIAL_CHARS),
                'passwordConfirm' => filter_input(INPUT_POST, 'passwordConfirm', FILTER_SANITIZE_SPECIAL_CHARS),
            ];

            $errors = $this->registerFormValidator->validate($postData);
            $valid = $errors['valid'];
            if (true === $valid) {
                $registered = $this->securityHelper->register($postData);

                if ($registered) {
                    header('Location: /login');

                    exit;
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
     * @param null|mixed $message
     * @param mixed      $username
     */
    public function userProfile($username, $message = null)
    {
        $sh = new StringHelper();
        $url = $_SERVER['REQUEST_URI'];
        $username = $sh->getLastUrlPart($url);
        Debugger::barDump($username);
        $user = $this->userManager->findBy(['username' => $username]);
        $loggedUser = null;
        if (isset($_SESSION['user'])) {
            $loggedUser = $_SESSION['user'];
        }
        $data = [
            'title' => 'MyBlog - Profile',
            'route' => 'profile',
            'user' => $user,
            'loggedUser' => $loggedUser,
            'session' => $this->session,
        ];

        $this->twig->render('pages/profile/profile.html.twig', $data);
    }

    /**
     * Logout the user.
     */
    public function logout()
    {
        $this->securityHelper->destroySession();
        header('Location: /blog');

        exit;
    }

    private function authenticate(): void
    {
        $middleware = new AuthenticationMiddleware($this->securityHelper);

        $middleware();
    }
}
