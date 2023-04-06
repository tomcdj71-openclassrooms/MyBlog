<?php

declare(strict_types=1);

namespace App\Controller;

use App\Config\DatabaseConnexion;
use App\Helper\TwigHelper;
use App\Manager\UserManager;

class UserController extends TwigHelper
{
    protected $userManager;

    public function __construct()
    {
        $db = new DatabaseConnexion();
        $this->userManager = new UserManager($db);
    }

    public function login($message = null)
    {
        $data = [
            'title' => 'MyBlog - Login',
            'route' => 'login',
        ];
        $twig = new TwigHelper();
        $twig->render('pages/security/login.html.twig', $data);
    }

    public function register($message = null)
    {
        $data = [
            'title' => 'MyBlog - Register',
            'route' => 'register',
        ];
        $twig = new TwigHelper();
        $twig->render('pages/security/register.html.twig', $data);
    }

    /*
    * Display the profile page.
    *
    * @param null $message
    */
    public function profile($message = null)
    {
        $data = [
            'title' => 'MyBlog - Profile',
            'message' => $message,
            'route' => 'profile',
        ];
        $twig = new TwigHelper();
        $twig->render('pages/profile/profile.html.twig', $data);
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
}
