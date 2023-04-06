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

    public function findBy(array $criteria): array
    {
        try {
            $sql = 'SELECT id, username, avatar, role, bio FROM user';

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

            $users = [];

            if ($data = $statement->fetch(\PDO::FETCH_ASSOC)) {
                return [
                    'id' => $data['id'],
                    'username' => $data['username'],
                    'avatar' => $data['avatar'],
                    'role' => $data['role'],
                    'bio' => $data['bio'],
                ];
            }

            return null;
        } catch (\PDOException $e) {
            return 'Error: '.$e->getMessage();
        }
    }
}
