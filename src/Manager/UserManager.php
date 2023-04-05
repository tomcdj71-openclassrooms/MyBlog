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

    public function findByRole(string $role): ?UserModel
    {
        try {
            $sql = 'SELECT * FROM user WHERE role = :role';

            $statement = $this->db->prepare($sql);
            $statement->execute(['role' => $role]);

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

    public function findByUsername(string $username): ?UserModel
    {
        try {
            $sql = 'SELECT * FROM user WHERE username = :username';

            $statement = $this->db->prepare($sql);
            $statement->execute(['username' => $username]);

            if ($data = $statement->fetch(\PDO::FETCH_ASSOC)) {
                return new UserModel(
                    (int) $data['id'],
                    $data['username'],
                    $data['email'],
                    $data['password'],
                    $data['created_at'],
                    $data['role'],
                    $data['avatar'],
                    [],
                    [],
                    $data['bio']
                );
            }
        } catch (\PDOException $e) {
            echo $e->getMessage();
        }
    }
}
