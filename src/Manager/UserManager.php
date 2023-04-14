<?php

declare(strict_types=1);

namespace App\Manager;

use App\Config\DatabaseConnexion;
use App\Model\UserModel;

class UserManager
{
    private $db;

    public function __construct(DatabaseConnexion $databaseConnexion)
    {
        $this->db = $databaseConnexion->connect();
    }

    public function getDatabase()
    {
        return $this->db;
    }

    public function find(int $id): ?UserModel
    {
        try {
            $sql = 'SELECT * FROM user WHERE id = :id';

            $statement = $this->db->prepare($sql);
            $statement->execute(['id' => $id]);

            if ($data = $statement->fetch(\PDO::FETCH_ASSOC)) {
                return $this->createUserModelFromArray($data);
            }
        } catch (\PDOException $e) {
            echo $e->getMessage();
        }
    }

    public function findAll(): array
    {
        try {
            $sql = 'SELECT * FROM user';

            $statement = $this->db->prepare($sql);
            $statement->execute();

            $users = [];
            while ($data = $statement->fetch(\PDO::FETCH_ASSOC)) {
                $users[] = $this->createUserModelFromArray($data);
            }

            return $users;
        } catch (\PDOException $e) {
            echo $e->getMessage();

            return [];
        }
    }

    public function findBy(array $criteria): ?UserModel
    {
        try {
            $sql = 'SELECT * FROM user';

            $conditions = [];
            $params = [];

            if (isset($criteria['username'])) {
                $conditions[] = 'username = :username';
                $params['username'] = $criteria['username'];
            }

            if (isset($criteria['email'])) {
                $conditions[] = 'email = :email';
                $params['email'] = $criteria['email'];
            }

            if (isset($criteria['role'])) {
                $conditions[] = 'role = :role';
                $params['role'] = $criteria['role'];
            }

            if (isset($criteria['remember_me_token'])) {
                $conditions[] = 'remember_me_token = :remember_me_token';
                $params['remember_me_token'] = $criteria['remember_me_token'];
            }

            if (!empty($conditions)) {
                $sql .= ' WHERE '.implode(' AND ', $conditions);
            }
            $sql .= ' LIMIT 1';

            $statement = $this->db->prepare($sql);
            $statement->execute($params);

            if ($data = $statement->fetch(\PDO::FETCH_ASSOC)) {
                return $this->createUserModelFromArray($data);
            }

            return null;
        } catch (\PDOException $e) {
            return 'Error: '.$e->getMessage();
        }
    }

    public function createUser(array $userData): ?UserModel
    {
        try {
            $sql = 'INSERT INTO user (username, email, password, created_at, role, avatar, bio)
                VALUES (:username, :email, :password, :created_at, :role, :avatar, :bio)';

            $statement = $this->db->prepare($sql);

            $params = [
                'username' => $userData['username'],
                'email' => $userData['email'],
                'password' => $userData['password'],
                'created_at' => date('Y-m-d H:i:s'),
                'role' => $userData['role'] ?? 'ROLE_USER',
                'avatar' => $userData['avatar'] ?? 'https:// i.pravatar.cc/150?img=6',
                'bio' => $userData['bio'] ?? '',
                'twitter' => $userData['twitter'] ?? '',
                'facebook' => $userData['facebook'] ?? '',
                'linkedin' => $userData['linkedin'] ?? '',
                'github' => $userData['github'] ?? '',
            ];

            $statement->execute($params);
            $lastInsertId = $this->db->lastInsertId();

            // Return the newly created user object
            $user = $this->find((int) $lastInsertId);

            if (null !== $user) {
                return $user;
            }
        } catch (\PDOException $e) {
            return null;
        }
    }

    public function setRememberMeToken(int $userId, string $token, int $expiresAt): void
    {
        $sql = 'UPDATE user SET remember_me_token = :token, remember_me_expires_at = :expires_at WHERE id = :id';
        $statement = $this->db->prepare($sql);
        $statement->execute([
            'id' => $userId,
            'token' => $token,
            'expires_at' => date('Y-m-d H:i:s', $expiresAt),
        ]);
    }

    public function updateProfile(UserModel $user, array $data): bool
    {
        $sql = '
        UPDATE user 
        SET 
            email = :email,
            firstName = :firstName,
            lastName = :lastName,
            bio = :bio,
            twitter = :twitter,
            facebook = :facebook,
            github = :github,
            avatar = :avatar,
            linkedin = :linkedin';

        $params = [
            ':email' => $data['email'] ?? $user->getEmail(),
            ':firstName' => $data['firstName'] ?? $user->getFirstName(),
            ':lastName' => $data['lastName'] ?? $user->getLastName(),
            ':bio' => $data['bio'] ?? $user->getBio(),
            ':avatar' => $data['avatar'] ?? $user->getAvatar(),
            ':twitter' => $data['twitter'] ?? $user->getTwitter(),
            ':facebook' => $data['facebook'] ?? $user->getFacebook(),
            ':github' => $data['github'] ?? $user->getGithub(),
            ':linkedin' => $data['linkedin'] ?? $user->getLinkedin(),
            ':id' => $user->getId(),
        ];

        $sql .= ' WHERE id = :id';

        $stmt = $this->db->prepare($sql);

        return $stmt->execute($params);
    }

    private function createUserModelFromArray(array $data): UserModel
    {
        if (null === $data['avatar']) {
            $avatar = '';
        }

        return new UserModel(
            (int) $data['id'],
            $data['username'],
            $data['email'],
            $data['password'],
            $data['created_at'],
            $data['role'],
            $data['avatar'] ?? '',
            $data['bio'],
            $data['remember_me_token'],
            $data['remember_me_expires_at'],
            $data['firstName'],
            $data['lastName'],
            $data['twitter'] ?? '',
            $data['facebook'] ?? '',
            $data['linkedin'] ?? '',
            $data['github'] ?? ''
        );
    }
}
