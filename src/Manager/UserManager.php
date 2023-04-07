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
                return new UserModel(
                    (int) $data['id'],
                    $data['username'],
                    $data['email'],
                    $data['password'],
                    $data['created_at'],
                    $data['role'],
                    $data['avatar'],
                    $data['posts'],
                    $data['comments'],
                    $data['bio']
                );
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
                $users[] = new UserModel(
                    (int) $data['id'],
                    $data['username'],
                    $data['email'],
                    $data['password'],
                    $data['created_at'],
                    $data['role'],
                    $data['avatar'],
                    $data['posts'],
                    $data['comments'],
                    $data['bio']
                );
            }

            return $users;
        } catch (\PDOException $e) {
            echo $e->getMessage();
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

            if (!empty($conditions)) {
                $sql .= ' WHERE '.implode(' AND ', $conditions);
            }
            $sql .= ' LIMIT 1';

            $statement = $this->db->prepare($sql);
            $statement->execute($params);

            if ($data = $statement->fetch(\PDO::FETCH_ASSOC)) {
                return new UserModel(
                    $data['id'],
                    $data['username'],
                    $data['email'],
                    $data['password'],
                    $data['created_at'],
                    $data['role'],
                    $data['avatar'],
                    $data['bio']
                );
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
                'created_at' => date('Y-m-d H:i:s'), // Assuming you want to set the current date and time
                'role' => $userData['role'] ?? 'ROLE_USER', // Set the default role if not provided
                'avatar' => $userData['avatar'] ?? 'https:// i.pravatar.cc/150?img=6', // Set the default avatar if not provided
                'bio' => $userData['bio'] ?? '',
            ];

            $statement->execute($params);

            $lastInsertId = $this->db->lastInsertId();

            // Return the newly created user object
            return new UserModel(
                (int) $lastInsertId,
                $userData['username'],
                $userData['email'],
                $userData['password'],
                $params['created_at'],
                $params['role'],
                $params['avatar'],
                $params['bio']
            );
        } catch (\PDOException $e) {
            return null;
        }
    }
}
