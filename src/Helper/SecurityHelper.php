<?php

declare(strict_types=1);

namespace App\Helper;

use App\Manager\UserManager;
use App\Model\UserModel;
use App\Router\Session;
use App\Service\CsrfTokenService;

class SecurityHelper
{
    private UserManager $userManager;
    private Session $session;
    private CsrfTokenService $csrfTokenService;

    public function __construct(UserManager $userManager, Session $session)
    {
        $this->userManager = $userManager;
        $this->session = $session;
        $this->csrfTokenService = new CsrfTokenService($session);
    }

    public function registerUser(array $postData): bool
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
        $user = $this->authenticateUser([
            'email' => $user->getEmail(),
            'password' => $postData['password'],
            'remember' => 'true',
        ]);
        if (!$user) {
            return false;
        }

        return true;
    }

    public function authenticateUser(array $data, bool $remember = false): ?UserModel
    {
        $user = $this->userManager->findOneBy(['email' => $data['email']]);
        if (!$user || !password_verify($data['password'], $user->getPassword())) {
            return null;
        }
        $this->session->regenerateId();
        $this->session->set('user', $user);
        if ($remember) {
            $this->createRememberMeToken($user);
        }

        return $user;
    }

    public function getUser(): ?UserModel
    {
        return $this->session->get('user');
    }

    public function createRememberMeToken(UserModel $user): void
    {
        $token = $this->csrfTokenService->generateToken('remember_me_token');
        $expiresAt = time() + 3600 * 24 * 30; // 30 days
        $this->userManager->setRememberMeToken($user->getId(), $token, $expiresAt);
        $this->session->remove('remember_me_token');

        setcookie('remember_me_token', $token, $expiresAt, '/', '', false, true);
    }

    public function validateAndReturnUserFromRememberMeToken(): ?UserModel
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

    public function hasRole(string $role): bool
    {
        $user = $this->getUser();

        return $user && $user->getRole() === $role;
    }
}
