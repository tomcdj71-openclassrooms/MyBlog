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
        ]);
        if (!$user) {
            return false;
        }

        return true;
    }

    public function authenticateUser(array $data): ?UserModel
    {
        $user = $this->userManager->findOneBy(['email' => $data['email']]);
        if (!$user || !password_verify($data['password'], $user->getPassword())) {
            return null;
        }
        $this->session->regenerateId();
        $this->session->set('user', $user);

        return $user;
    }

    public function getUser(): ?UserModel
    {
        return $this->session->get('user');
    }

    public function hasRole(string $role): bool
    {
        $user = $this->getUser();

        return $user && $user->getRole() === $role;
    }
}
