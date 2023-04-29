<?php

declare(strict_types=1);

namespace App\Manager;

use App\Config\DatabaseConnexion;
use App\Model\UserModel;
use App\ModelParameters\UserModelParameters;

class UserManager
{
    private $database;
    private UserModelParameters $userModelParams;

    public function __construct(DatabaseConnexion $databaseConnexion)
    {
        $this->database = $databaseConnexion->connect();
        $this->userModelParams = new UserModelParameters();
    }

    public function find(int $id): ?UserModel
    {
        try {
            $sql = 'SELECT * FROM user WHERE id = :id';
            $statement = $this->database->prepare($sql);
            $statement->execute(['id' => $id]);
            $data = $statement->fetch(\PDO::FETCH_ASSOC);
            if (!$data) {
                return null;
            }

            return $this->createUserModelFromArray($data);
        } catch (\PDOException $error) {
            throw new \PDOException($error->getMessage(), (int) $error->getCode());
        }
    }

    public function findByPage(int $page, int $limit): array
    {
        try {
            $sql = 'SELECT * FROM user ORDER BY id DESC LIMIT :limit OFFSET :offset';
            $statement = $this->database->prepare($sql);
            $statement->bindValue('limit', $limit, \PDO::PARAM_INT);
            $statement->bindValue('offset', ($page - 1) * $limit, \PDO::PARAM_INT);
            $statement->execute();
            $data = $statement->fetchAll(\PDO::FETCH_ASSOC);
            if (!$data) {
                return [];
            }
            $users = [];
            foreach ($data as $user) {
                $users[] = $this->createUserModelFromArray($user);
            }

            return $users;
        } catch (\PDOException $error) {
            throw new \PDOException($error->getMessage(), (int) $error->getCode());
        }
    }

    public function findOneBy(array $criteria): ?UserModel
    {
        try {
            $sql = 'SELECT * FROM user WHERE ';
            $where = [];
            $parameters = [];
            foreach ($criteria as $key => $value) {
                $where[] = $key.' = :'.$key;
                $parameters[$key] = $value;
            }
            $sql .= implode(' AND ', $where);
            $statement = $this->database->prepare($sql);
            $statement->execute($parameters);
            $data = $statement->fetch(\PDO::FETCH_ASSOC);
            if (!$data) {
                return null;
            }

            return $this->createUserModelFromArray($data);
        } catch (\PDOException $error) {
            throw new \PDOException($error->getMessage(), (int) $error->getCode());
        }
    }

    public function findAll(): array
    {
        try {
            $sql = 'SELECT * FROM user';
            $statement = $this->database->prepare($sql);
            $statement->execute();
            $data = $statement->fetchAll(\PDO::FETCH_ASSOC);
            if (!$data) {
                return [];
            }
            $users = [];
            foreach ($data as $user) {
                $users[] = $this->createUserModelFromArray($user);
            }

            return $users;
        } catch (\PDOException $error) {
            throw new \PDOException($error->getMessage(), (int) $error->getCode());
        }
    }

    public function createUser(array $userData): ?UserModel
    {
        try {
            $sql = 'INSERT INTO user (username, email, password, created_at, role, avatar, bio, twitter, facebook, linkedin, github)
            VALUES (:username, :email, :password, :created_at, :role, :avatar, :bio, :twitter, :facebook, :linkedin, :github)';
            $statement = $this->database->prepare($sql);
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
            $lastInsertId = $this->database->lastInsertId();
            $user = $this->find((int) $lastInsertId);
            if (null !== $user) {
                return $user;
            }
        } catch (\PDOException $error) {
            return null;
        }
    }

    public function setRememberMeToken(int $userId, string $token, int $expiresAt): void
    {
        $sql = 'UPDATE user SET remember_me_token = :token, remember_me_expires_at = :expires_at WHERE id = :id';
        $statement = $this->database->prepare($sql);
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
        $statement = $this->database->prepare($sql);

        return $statement->execute($params);
    }

    public function createUserModelFromArray(array $data): UserModel
    {
        $userModelParams = $this->userModelParams->createFromData($data);

        return new UserModel($userModelParams);
    }
}
