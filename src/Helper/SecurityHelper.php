<?php

namespace App\Helper;

use App\Config\DatabaseConnexion;
use App\Manager\UserManager;
use App\Model\UserModel;
use App\Validator\RegisterFormValidator;

/**
 * This helper class provides methods for authenticating users.
 * This handles the login and registration process.
 * And also handle the user session.
 */
class SecurityHelper
{
    private $userManager;
    private $db;

    public function __construct()
    {
        $this->db = new DatabaseConnexion();
        $this->userManager = new UserManager($this->db);
    }

    /**
     * Attempt to authenticate a user with the provided username and password.
     */
    public function authenticate(string $username, string $password): bool
    {
        // Find the user by username
        $user = $this->userManager->findBy(['username' => $username]);

        // Verify the password
        if ($user && password_verify($password, $user->getPassword())) {
            // Log the user in
            $this->login($user);

            return true;
        }

        return false;
    }

    /**
     * Register a new user with the provided data.
     *
     * @param array $userData
     *
     * @return mixed User object on success, string error message on failure
     */
    public function register(array $data)
    {
        // Create an instance of the RegisterFormValidator
        $validator = new RegisterFormValidator($data);

        // Validate the form data
        if ($validator->validate($data)) {
            // Check if the email already exists
            $existingEmail = $this->userManager->findBy(['email' => $data['email']]);
            if ($existingEmail) {
                throw new \Exception('Email is already in use.');
            }

            // Check if the username already exists
            $existingUsername = $this->userManager->findBy(['username' => $data['username']]);
            if ($existingUsername) {
                throw new \Exception('Username is already in use.');
            }

            // Hash the password
            $hashedPassword = password_hash($data['password'], PASSWORD_DEFAULT);

            // Prepare user data
            $userData = [
                'username' => $data['username'],
                'email' => $data['email'],
                'password' => $hashedPassword,
            ];

            // Create the new user
            $newUser = $this->userManager->createUser($userData);

            if ($newUser) {
                // Log the user in by starting a session
                session_start();
                $_SESSION['user'] = [
                    'id' => $newUser->getId(),
                    'username' => $newUser->getUsername(),
                    'email' => $newUser->getEmail(),
                    'role' => $newUser->getRole(),
                ];

                return true;
            }

            throw new \Exception('An error occurred during registration.');
        }

        throw new \Exception('Invalid form data.');
    }

    /**
     * Start the session if it's not already started.
     */
    public function startSession()
    {
        if (PHP_SESSION_NONE === session_status()) {
            session_start();
        }
    }

    /**
     * Destroy the current session.
     */
    public function destroySession()
    {
        if (PHP_SESSION_ACTIVE === session_status()) {
            session_unset();
            session_destroy();
        }
    }

    /**
     * Deny access to a page until a specific role is granted.
     */
    public function denyAccessUntilGranted(string $role, callable $callback)
    {
        $this->startSession();

        if (!$this->isAuthenticated() || !$this->hasRole($role)) {
            header('Location: /login');

            exit;
        }

        return $callback();
    }

    /**
     * Check if the user has a specific role.
     */
    public function hasRole(string $role): bool
    {
        if (!isset($_SESSION['user'])) {
            return false;
        }

        $user = $_SESSION['user'];
        $roles = explode(',', $user->getRole());

        return in_array($role, $roles, true);
    }

    /**
     * Check if a user is authenticated (logged in).
     */
    public function isAuthenticated(): bool
    {
        $message = isset($_SESSION['user']) && $_SESSION['user'] instanceof UserModel ? 'You are logged in' : 'You are not logged in';

        return isset($_SESSION['user'], $message);
    }

    public function login(UserModel $user)
    {
        // Start a session if it hasn't been started already
        if (PHP_SESSION_NONE === session_status()) {
            session_start();
        }

        // Set the session user data
        $_SESSION['user'] = $user;
    }

    public function getSession()
    {
        return $_SESSION;
    }
}
