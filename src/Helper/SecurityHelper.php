<?php

declare(strict_types=1);

namespace App\Helper;

use App\Config\DatabaseConnexion;
use App\Manager\UserManager;
use App\Model\UserModel;
use App\Validator\LoginFormValidator;
use App\Validator\RegisterFormValidator;

class SecurityHelper
{
    protected $userManager;
    protected $registerValidator;
    protected $loginValidator;

    public function __construct()
    {
        $db = new DatabaseConnexion();
        $this->userManager = new UserManager($db);
        $this->registerValidator = new RegisterFormValidator();
        $this->loginValidator = new LoginFormValidator($this->userManager);
    }

    public function startSession(): void
    {
        if (PHP_SESSION_NONE === session_status()) {
            session_start();
        }
    }

    public function register(array $postData): bool
    {
        // Validate the user input
        $response = $this->registerValidator->validate($postData);
        if (!empty($response['errors'] || false === $response['valid'])) {
            return false;
        }

        // Create a new user
        $userData = [
            'username' => $postData['username'],
            'email' => $postData['email'],
            'password' => password_hash($postData['password'], PASSWORD_DEFAULT),
            'role' => 'ROLE_USER',
            'avatar' => 'https://i.pravatar.cc/150?img=6',
        ];

        $user = $this->userManager->createUser($userData);

        if (!$user instanceof UserModel) {
            return false;
        }

        $authErrors = $this->authenticate([
            'email' => $user->getEmail(),
            'password' => $postData['password'],
            'remember' => 'true',
        ]);

        if (!$authErrors) {
            header('Location: /blog');

            exit;
        }

        return true;
    }

    public function authenticate(array $data, bool $remember = false): ?UserModel
    {
        // Validate the login form data

        $errors = $this->loginValidator->validate([
            'email' => $data['email'],
            'password' => $data['password'],
            'remember' => $remember ? 'true' : 'false',
        ]);

        if (!empty($errors)) {
            return $errors;
        }

        // Find the user in the database
        $user = $this->userManager->findBy(['email' => $data['email']]);

        if (!$user || !password_verify($data['password'], $user->getPassword())) {
            return null;
        }

        // Set the user ID in the session

        $this->setSessionValue('user', $user);

        if ($this->loginValidator->shouldRemember(['email' => $data['email'], 'password' => $data['password'], 'remember' => $remember ? 'true' : 'false'])) {
            $this->rememberMe($user);
        }

        return $user;
    }

    public function rememberMe(UserModel $user): void
    {
        $token = bin2hex(random_bytes(16));
        $expiresAt = time() + 3600 * 24 * 30; // 30 days

        $this->userManager->setRememberMeToken($user->getId(), $token, $expiresAt);

        setcookie('remember_me_token', $token, $expiresAt, '/', '', false, true);
    }

    public function setSessionValue(string $key, $value): void
    {
        if (!isset($_SESSION)) {
            session_start();
        }

        $_SESSION[$key] = $value;
    }

    public function getSessionValue(string $key)
    {
        if (!isset($_SESSION)) {
            session_start();
        }

        return $_SESSION[$key] ?? null;
    }

    public function removeSessionValue(string $key): void
    {
        if (!isset($_SESSION)) {
            session_start();
        }

        unset($_SESSION[$key]);
    }

    public function destroySession(): void
    {
        if (!isset($_SESSION)) {
            session_start();
        }

        session_destroy();
    }

    public function denyAccessUntilGranted(string $role, callable $callback): void
    {
        if (!$this->isGranted($role)) {
            header('Location: /login');

            exit;
        }

        $callback();
    }

    public function isGranted(string $role): bool
    {
        if (!isset($_SESSION)) {
            session_start();
        }

        if (!isset($_SESSION['user'])) {
            return false;
        }

        return $role === $_SESSION['user']->getRole();
    }

    public function getUser(): ?UserModel
    {
        if (!isset($_SESSION)) {
            session_start();
        }

        if (!isset($_SESSION['user'])) {
            return null;
        }

        return $_SESSION['user'];
    }

    public function getSession(): ?array
    {
        if (PHP_SESSION_NONE === session_status()) {
            session_start();
        }

        return $_SESSION ?? null;
    }

    public function isAuthenticated(): bool
    {
        return null !== $this->getSessionValue('user_id');
    }

    public function getAuthenticatedUser()
    {
        $userId = $this->getSessionValue('user_id');

        if ($userId) {
            $user = $this->userManager->findBy(['id' => $userId]);

            if ($user) {
                return new UserModel(
                    $user['id'],
                    $user['username'],
                    $user['email'],
                    $user['password'],
                    $user['created_at'],
                    $user['role'],
                    $user['firstName'],
                    $user['lastName'],
                    $user['avatar'],
                    $user['bio'],
                    $user['twitter'],
                    $user['facebook'],
                    $user['github'],
                    $user['linkedin'],
                    $user['remember_me_token'],
                    $user['remember_me_expires_at']
                );
            }
        }

        return null;
    }

    public function createRememberMeToken(): array
    {
        $token = bin2hex(random_bytes(32));
        $expiresAt = time() + 86400 * 7; // 7 days from now

        return ['token' => $token, 'expiresAt' => $expiresAt];
    }

    public function getRememberMeToken(): ?string
    {
        if (isset($_COOKIE['remember_me_token'])) {
            if (empty($_COOKIE['remember_me_token'])) {
                throw new \InvalidArgumentException('Cookie is empty');
            }

            return $_COOKIE['remember_me_token'];
        }

        throw new \InvalidArgumentException('Cookie is not set');
    }

    public function checkRememberMeToken(string $token): ?UserModel
    {
        $token = $this->getRememberMeToken();

        if (!$token) {
            throw new \Exception('No token found.');
        }

        $user = $this->userManager->findBy(['remember_me_token' => $token]);

        if (!$user) {
            throw new \Exception('No user found.');
        }

        $expiresAt = $user->getRememberMeExpiresAt();
        $expiresAt = strtotime($expiresAt);
        if ($expiresAt < time()) {
            throw new \Exception('Token expired.');
        }

        $this->setSessionValue('user', $user);
        header('Location: /blog');

        exit;

        return $user;
    }
}
