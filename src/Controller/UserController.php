<?php

declare(strict_types=1);

namespace App\Controller;

use App\Config\DatabaseConnexion;
use App\Helper\ImageHelper;
use App\Helper\SecurityHelper;
use App\Helper\StringHelper;
use App\Helper\TwigHelper;
use App\Manager\UserManager;
use App\Model\UserModel;
use App\Validator\EditProfileFormValidator;
use App\Validator\LoginFormValidator;
use App\Validator\RegisterFormValidator;

class UserController extends TwigHelper
{
    protected $userManager;
    protected $loginValidator;
    protected $registerValidator;
    protected $securityHelper;
    private $session;
    private $editProfileFormValidator;
    private $imageHelper;

    public function __construct()
    {
        $db = new DatabaseConnexion();
        $this->userManager = new UserManager($db);
        $this->loginValidator = new LoginFormValidator($this->userManager);
        $this->registerValidator = new RegisterFormValidator($this->userManager);
        $this->securityHelper = new SecurityHelper();
        $this->session = $this->securityHelper->getSession();
        $this->editProfileFormValidator = new EditProfileFormValidator($this->userManager);
        $this->imageHelper = new ImageHelper('uploads/avatars/', 200, 200);
    }

    /*
    * Display the profile page.
    *
    * @param null $message
    */
    public function profile($message = null)
    {
        $twig = new TwigHelper();
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

        $validator = new EditProfileFormValidator();

        if ('POST' === $_SERVER['REQUEST_METHOD']) {
            $postData = [
                'firstName' => $_POST['firstName'] ?? '',
                'lastName' => $_POST['lastName'] ?? '',
                'email' => $_POST['email'] ?? '',
                'username' => $_POST['username'] ?? '',
                'bio' => $_POST['bio'] ?? '',
                'avatar' => $_FILES['avatar'] ?? null,
                'twitter' => $_POST['twitter'] ?? '',
                'facebook' => $_POST['facebook'] ?? '',
                'github' => $_POST['github'] ?? '',
                'linkedin' => $_POST['linkedin'] ?? '',
            ];

            $response = $validator->validate($postData);

            if ($response['valid']) {
                $data = $response['data'];

                if (null !== $data['avatar']) {
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
                    $data = [
                        'title' => 'MyBlog - Profile',
                        'route' => 'profile',
                        'user' => $user,
                        'message' => $message,
                        'errors' => $errors,
                        'loggedUser' => $loggedUser,
                    ];

                    $twig->render('pages/profile/profile.html.twig', $data);

                    exit;
                }

                $errors = $response['errors'];
            } else {
                $errors = $response['errors'];
            }
        } else {
            $errors = [];
        }

        $user = $this->userManager->find($userId);

        $data = [
            'title' => 'MyBlog - Profile',
            'route' => 'profile',
            'user' => $user,
            'message' => $message,
            'errors' => $errors,
            'loggedUser' => $loggedUser,
        ];
        $twig->render('pages/profile/profile.html.twig', $data);
    }

    /**
     * Display the login page.
     *
     * @param null $message
     */
    public function login($message = null)
    {
        $twig = new TwigHelper();
        $securityHelper = new SecurityHelper();
        $validator = new LoginFormValidator($this->userManager);

        try {
            if (isset($_COOKIE['remember_me_token']) && !$securityHelper->isAuthenticated()) {
                $securityHelper->checkRememberMeToken($_COOKIE['remember_me_token']);
            }
        } catch (\Exception $e) {
            // If there is an issue with the remember_me_token (expired or invalid), remove it
            setcookie('remember_me_token', '', time() - 3600, '/', '', false, true);
        }

        if ($securityHelper->isAuthenticated()) {
            header('Location: /profile');

            exit;

            $data = [
                'title' => 'MyBlog - Profile',
                'route' => 'profile',
                'message' => $message,
            ];

            $twig->render('pages/profile/profile.html.twig', $data);
        } else {
            if ('POST' === $_SERVER['REQUEST_METHOD']) {
                $postData = [
                    'email' => $_POST['email'],
                    'password' => $_POST['password'],
                    'remember' => isset($_POST['remember']) && 'true' === $_POST['remember'],
                ];

                $errors = $validator->validate($postData, $postData['remember']);

                if (empty($errors)) {
                    $user = $securityHelper->authenticate($postData);

                    if ($user instanceof UserModel) {
                        if ($postData['remember']) {
                            $securityHelper->rememberMe($user);
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

                $twig->render('pages/security/login.html.twig', $data);
            }
        }
    }

    /**
     * Display the register page.
     *
     * @param null $message
     */
    public function register($message = null)
    {
        $twig = new TwigHelper();
        $validator = new RegisterFormValidator();

        if ('POST' === $_SERVER['REQUEST_METHOD']) {
            $postData = [
                'email' => $_POST['email'],
                'username' => $_POST['username'],
                'password' => $_POST['password'],
                'passwordConfirm' => $_POST['passwordConfirm'],
            ];

            $errors = $validator->validate($postData);
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
            ];

            $twig->render('pages/security/register.html.twig', array_merge($data, ['errors' => $errors]));
        } else {
            $data = [
                'title' => 'MyBlog - Creer un compte',
                'route' => 'register',
                'message' => $message,
            ];
            $twig->render('pages/security/register.html.twig', $data);
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
        $twig = new TwigHelper();
        $sh = new StringHelper();
        $url = $_SERVER['REQUEST_URI'];
        $username = $sh->getLastUrlPart($url);

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
        ];

        $twig->render('pages/profile/profile.html.twig', $data);
    }

    /**
     * Logout the user.
     */
    public function logout()
    {
        $securityHelper = new SecurityHelper();
        $securityHelper->destroySession();
        header('Location: /blog');

        exit;
    }
}
