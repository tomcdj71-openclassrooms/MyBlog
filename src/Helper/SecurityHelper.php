<?php

declare(strict_types=1);

namespace App\Helper;

use App\Manager\UserManager;
use App\Model\UserModel;
use App\Router\Session;

class SecurityHelper
{
    private UserManager $userManager;
    private Session $session;

    public function __construct(UserManager $userManager, Session $session)
    {
        $this->userManager = $userManager;
        $this->session = $session;
    }

    public function register(array $postData): bool
    {
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
        $user = $this->authenticate([
            'email' => $user->getEmail(),
            'password' => $postData['password'],
            'remember' => 'true',
        ]);
        if (!$user) {
            return false;
        }

        return true;
    }

    public function authenticate(array $data, bool $remember = false): ?UserModel
    {
        $user = $this->userManager->findOneBy(['email' => $data['email']]);
        if (!$user || !password_verify($data['password'], $user->getPassword())) {
            return null;
        }
        $this->session->regenerateId();
        $this->session->set('user', $user);
        if ($remember) {
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
        $token = $this->generateToken(16);
        $expiresAt = time() + 3600 * 24 * 30; // 30 days
        $this->userManager->setRememberMeToken($user->getId(), $token, $expiresAt);

        setcookie('remember_me_token', $token, $expiresAt, '/', '', false, true);
    }

    public function checkRememberMeToken(): ?UserModel
    {
        if (empty($this->session->get('remember_me_token'))) {
            throw new \InvalidArgumentException("Le jeton 'Remember Me' n'est pas défini ou vide.");
        }
        $token = $this->session->getCookie('remember_me_token');
        $user = $this->userManager->findOneBy(['remember_me_token' => $token]);
        if (!$user) {
            throw new \Exception('Aucun utilisateur trouvé.');
        }
        $expiresAt = $user->getRememberMeExpires();
        $expiresAt = strtotime($expiresAt);
        if ($expiresAt < time()) {
            throw new \Exception('Le jeton a expiré.');
        }
        $this->session->regenerateId();
        $this->session->set('user', $user);

        return $user;
    }

    public function loginById(int $userId): ?UserModel
    {
        $user = $this->userManager->findOneBy(['id' => $userId]);
        if (!$user) {
            return null;
        }
        $this->session->regenerateId();
        $this->session->set('user', $user);

        return $user;
    }

    public function hasRole(string $role): bool
    {
        $user = $this->getUser();

        return $user && $user->getRole() === $role;
    }

    private function generateToken(int $length = 32): string
    {
        return bin2hex(random_bytes($length));
    }
}
