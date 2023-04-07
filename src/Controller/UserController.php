<?php

declare(strict_types=1);

namespace App\Controller;

use App\Config\DatabaseConnexion;
use App\Helper\SecurityHelper;
use App\Helper\TwigHelper;
use App\Manager\UserManager;
use App\Validator\LoginFormValidator;
use App\Validator\RegisterFormValidator;

class UserController extends TwigHelper
{
    protected $userManager;
    protected $loginValidator;
    protected $registerValidator;
    protected $securityHelper;

    public function __construct()
    {
        $db = new DatabaseConnexion();
        $this->userManager = new UserManager($db);
        $this->loginValidator = new LoginFormValidator($this->userManager);
        $this->registerValidator = new RegisterFormValidator($this->userManager);
        $this->securityHelper = new SecurityHelper();
    }

    /*
    * Display the profile page.
    *
    * @param null $message
    */
    public function profile($message = null)
    {
        $this->securityHelper->denyAccessUntilGranted('ROLE_USER', function () {
            $user = $_SESSION['user'];
            $data = [
                'title' => 'MyBlog - Profile',
                'route' => 'profile',
                'user' => $user,
            ];
            $twig = new TwigHelper();
            $twig->render('pages/profile/profile.html.twig', $data);
        });
    }

    public function login($message = null)
    {
        if (isset($_SESSION['user']) && !empty($_SESSION['user'])) {
            header('Location: /blog'); // Redirect to the home page or any other page as needed

            exit;
        }

        $twig = new TwigHelper();

        if ('POST' === $_SERVER['REQUEST_METHOD']) {
            $username = $_POST['username'];
            $password = $_POST['password'];
            $data = [
                'username' => $username,
                'password' => $password,
            ];
            $errors = $this->loginValidator->validate($data);

            if (empty($errors)) {
                $securityHelper = new SecurityHelper();
                $loggedIn = $securityHelper->authenticate($username, $password);
                if ($loggedIn) {
                    header('Location: /blog');

                    exit;
                }
                $errors['login'] = 'Invalid username or password';
            }
        }
        // $message is an array of errors
        $data = [
            'title' => 'MyBlog - Login',
            'route' => 'login',
            'message' => $message,
        ];

        $twig->render('pages/security/login.html.twig', $data);
    }

    /**
     * Display the register page.
     *
     * @param null $message
     */
    public function register($message = null)
    {
        if ('POST' === $_SERVER['REQUEST_METHOD']) {
            $email = $_POST['email'];
            $username = $_POST['username'];
            $password = $_POST['password'];
            $passwordConfirm = $_POST['passwordConfirm'];

            $validator = new RegisterFormValidator();
            $validationResult = $validator->validate([
                'email' => $email,
                'username' => $username,
                'password' => $password,
                'passwordConfirm' => $passwordConfirm,
            ]);

            if ($validationResult['valid']) {
                $securityHelper = new SecurityHelper();
                $securityHelper->register([
                    'email' => $email,
                    'username' => $username,
                    'password' => $password,
                ]);
                $message = 'You have been registered successfully.';
            } else {
                $message = $validationResult['errors'];
            }
        }

        $data = [
            'title' => 'MyBlog - Register',
            'route' => 'register',
            'message' => $message,
        ];
        $twig = new TwigHelper();

        $twig->render('pages/security/register.html.twig', $data);
    }

    /**
     * Display the user profile page.
     *
     * @param null|mixed $message
     * @param mixed      $username
     */
    public function userProfile($username, $message = null)
    {
        $user = $this->userManager->findBy(['username' => $username]);
        $data = [
            'title' => 'MyBlog - Profile',
            'route' => 'profile',
            'user' => $user,
        ];
        $twig = new TwigHelper();
        $twig->render('pages/profile/profile.html.twig', $data);
    }

    /**
     * Logout the user.
     */
    public function logout()
    {
        $securityHelper = new SecurityHelper();
        $securityHelper->destroySession();
        header('Location: /blog'); // Redirect to the blog page after logout

        exit;
    }
}
