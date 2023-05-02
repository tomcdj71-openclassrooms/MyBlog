<?php

declare(strict_types=1);

namespace App\Manager;

use App\Config\DatabaseConnexion;
use App\Model\TagModel;
use App\ModelParameters\TagModelParameters;

class TagManager
{
    private $database;
    private TagModelParameters $tagModelParams;

    public function __construct(DatabaseConnexion $databaseConnexion)
    {
        $this->database = $databaseConnexion->connect();
        $this->tagModelParams = new TagModelParameters();
    }

    public function getDatabase()
    {
        return $this->database;
    }

    public function findAll(): array
    {
        try {
            $sql = 'SELECT * FROM tag';
            $statement = $this->database->prepare($sql);
            $statement->execute();
            $tags = [];
            while ($data = $statement->fetch(\PDO::FETCH_ASSOC)) {
                $tags[] = $this->createTagModelFromArray($data);
            }

            return $tags;
        } catch (\PDOException $error) {
            throw new \PDOException($error->getMessage(), (int) $error->getCode());
        }
    }

    public function findBySlug(string $slug): ?TagModel
    {
        try {
            $sql = 'SELECT * FROM tag WHERE slug = :slug';
            $statement = $this->database->prepare($sql);
            $statement->execute(['slug' => $slug]);
            $data = $statement->fetch(\PDO::FETCH_ASSOC);
            if ($data) {
                return $this->createTagModelFromArray($data);
            }

            return null;
        } catch (\PDOException $error) {
            throw new \PDOException($error->getMessage(), (int) $error->getCode());
        }
    }

    public function count(): int
    {
        try {
            $sql = 'SELECT COUNT(*) FROM tag';
            $statement = $this->database->prepare($sql);
            $statement->execute();

            return (int) $statement->fetchColumn();
        } catch (\PDOException $error) {
            throw new \PDOException($error->getMessage(), (int) $error->getCode());
        }
    }

    private function createTagModelFromArray(array $data): TagModel
    {
        $tagModelParams = $this->tagModelParams->createFromData($data);

        return new TagModel($tagModelParams);
    }
}
