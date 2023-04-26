<?php

declare(strict_types=1);

namespace App\Helper;

use App\Config\DatabaseConnexion;
use App\Manager\UserManager;
use App\Model\UserModel;
use App\Router\Session;
use App\Validator\LoginFormValidator;
use App\Validator\RegisterFormValidator;

class SecurityHelper
{
    private UserManager $userManager;
    private RegisterFormValidator $registerValidator;
    private LoginFormValidator $loginValidator;
    private $session;

    public function __construct(Session $session)
    {
        $connexion = new DatabaseConnexion();
        $this->session = $session;
        $this->userManager = new UserManager($connexion);
        $this->registerValidator = new RegisterFormValidator($this);
        $this->loginValidator = new LoginFormValidator($this->userManager, $this);
    }

    public function register(array $postData): bool
    {
        $response = $this->registerValidator->validate($postData);

        if (!empty($response['errors']) || false === $response['valid']) {
            return false;
        }

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
        }

        return true;
    }

    public function authenticate(array $data, bool $remember = false): ?UserModel
    {
        $errors = $this->loginValidator->validate([
            'email' => $data['email'],
            'password' => $data['password'],
            'remember' => $remember ? 'true' : 'false',
        ]);

        if (!empty($errors)) {
            return $errors;
        }

        $user = $this->userManager->findOneBy(['email' => $data['email']]);

        if (!$user || !password_verify($data['password'], $user->getPassword())) {
            return null;
        }

        $this->session->regenerateId();
        $this->session->set('user', $user);

        if ($this->loginValidator->shouldRemember($data)) {
            $this->rememberMe($user);
        }

        return $user;
    }

    public function getUser(): ?UserModel
    {
        return $this->session->get('user');
    }

    public function rememberMe(UserModel $user): void
    {
        $token = bin2hex(random_bytes(16));
        $expiresAt = time() + 3600 * 24 * 30; // 30 days

        $this->userManager->setRememberMeToken($user->getId(), $token, $expiresAt);

        setcookie('remember_me_token', $token, $expiresAt, '/', '', false, true);
    }

    public function generateRememberMeToken(): array
    {
        $token = bin2hex(random_bytes(32));
        $expiresAt = time() + 86400 * 7;

        return ['token' => $token, 'expiresAt' => $expiresAt];
    }

    public function checkRememberMeToken(): ?UserModel
    {
        if (!isset($_COOKIE['remember_me_token']) || empty($_COOKIE['remember_me_token'])) {
            throw new \InvalidArgumentException('Remember me token is not set or empty.');
        }
        $token = $this->session->getCookie('remember_me_token');
        $user = $this->userManager->findOneBy(['remember_me_token' => $token]);
        if (!$user) {
            throw new \Exception('No user found.');
        }
        $expiresAt = $user->getRememberMeExpires();
        $expiresAt = strtotime($expiresAt);
        if ($expiresAt < time()) {
            throw new \Exception('Token expired.');
        }
        $this->session->set('user', $user);
        header('Location: /blog');

        return $user;
    }

    public function generateCsrfToken(string $key): string
    {
        $token = bin2hex(random_bytes(32));
        $csrfTokens = $this->session->get('csrf_tokens') ?? [];
        $csrfTokens[$key] = $token;
        $this->session->set('csrf_tokens', $csrfTokens);

        return $token;
    }

    public function checkCsrfToken(string $key, string $token): bool
    {
        $csrfTokens = $this->session->get('csrf_tokens');
        $expected = $csrfTokens[$key] ?? null;
        if (null === $expected) {
            throw new \InvalidArgumentException('No CSRF token found for the given key.');
        }

        return hash_equals($expected, $token);
    }
}
