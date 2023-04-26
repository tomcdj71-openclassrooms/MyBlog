<?php

declare(strict_types=1);

namespace App\Manager;

use App\Config\DatabaseConnexion;
use App\Model\TagModel;
use App\ModelParameters\TagModelParameters;

class TagManager
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

    public function findAll(): array
    {
        try {
            $sql = 'SELECT * FROM tag';

            $statement = $this->db->prepare($sql);
            $statement->execute();

            $tags = [];
            while ($data = $statement->fetch(\PDO::FETCH_ASSOC)) {
                $tags[] = $this->createTagModelFromArray($data);
            }

            return $tags;
        } catch (\PDOException $e) {
            echo $e->getMessage();
        }
    }

    public function find(int $id): ?TagModel
    {
        try {
            $sql = 'SELECT * FROM tag WHERE id = :id';
            $statement = $this->db->prepare($sql);
            $statement->execute(['id' => $id]);
            $data = $statement->fetch(\PDO::FETCH_ASSOC);
            if ($data) {
                $this->createTagModelFromArray($data);
            }

            return null;
        } catch (\PDOException $e) {
            echo $e->getMessage();
        }
    }

    public function findBySlug(string $slug): ?TagModel
    {
        try {
            $sql = 'SELECT * FROM tag WHERE slug = :slug';

            $statement = $this->db->prepare($sql);
            $statement->execute(['slug' => $slug]);

            if ($data = $statement->fetch(\PDO::FETCH_ASSOC)) {
                return $this->createTagModelFromArray($data);
            }

            return null;
        } catch (\PDOException $e) {
            echo $e->getMessage();
        }
    }

    public function count(): int
    {
        try {
            $sql = 'SELECT COUNT(*) FROM tag';

            $statement = $this->db->prepare($sql);
            $statement->execute();

            return (int) $statement->fetchColumn();
        } catch (\PDOException $e) {
            echo $e->getMessage();
        }
    }

    private function createTagModelFromArray(array $data): TagModel
    {
        $tagModelParams = TagModelParameters::createFromData($data);

        return new TagModel($tagModelParams);
    }
}
