<?php

declare(strict_types=1);

namespace App\Helper;

use App\Manager\UserManager;
use App\Model\UserModel;
use App\Router\HttpException;
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

    public function registerUser(array $formData): bool
    {
        $userData = [
            'username' => $formData['username'],
            'email' => $formData['email'],
            'password' => password_hash($formData['password'], PASSWORD_DEFAULT),
            'role' => 'ROLE_USER',
            'avatar' => 'https://i.pravatar.cc/150?img=6',
        ];
        $user = $this->userManager->createUser($userData);
        if (!$user instanceof UserModel) {
            return false;
        }
        $user = $this->authenticateUser([
            'email' => $user->getEmail(),
            'password' => $formData['password'],
        ]);
        if (!$user) {
            return false;
        }

        return true;
    }

    public function authenticateUser(array $data): array
    {
        $user = $this->userManager->findOneBy(['email' => $data['email']]);
        $errors = [];

        if (!$user || !password_verify($data['password'], $user->getPassword())) {
            $errors[] = 'Email ou mot de passe incorrect.';
        } else {
            $this->session->regenerateId();
            $this->session->set('user', $user);
        }

        return ['user' => $user, 'errors' => $errors];
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

    public function denyAccessUnlessAuthenticated(): void
    {
        $this->denyAccessUnless(
            fn () => $this->hasRole('ROLE_USER') || $this->hasRole('ROLE_ADMIN'),
            "Accès refusé. Vous n'avez pas la permission d'accéder à cette page."
        );
    }

    public function denyAccessUnlessAdmin(): void
    {
        $this->denyAccessUnless(
            fn () => $this->hasRole('ROLE_ADMIN'),
            "Accès refusé. Vous n'avez pas la permission d'accéder à cette page."
        );
    }

    public function denyAccessIfAuthenticated(): void
    {
        $this->denyAccessUnless(
            fn () => $this->isUserUnauthenticated(),
            'Accès refusé. Vous êtes déjà connecté.'
        );
    }

    protected function getUserWithRole(string $role = 'ROLE_USER'): ?UserModel
    {
        $user = $this->getUser();
        if (!$this->hasRole($role)) {
            return null;
        }

        return $user;
    }

    private function isUserUnauthenticated(): bool
    {
        return null === $this->getUser();
    }

    private function denyAccessUnless(callable $condition, string $message): void
    {
        if (!call_user_func($condition)) {
            throw new HttpException(403, $message);
        }
    }
}
