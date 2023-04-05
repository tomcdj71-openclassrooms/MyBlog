<?php

declare(strict_types=1);

namespace App\Manager;

use App\Config\DatabaseConnexion;
use App\Model\TagModel;

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
                $tags[] = new TagModel(
                    (int) $data['id'],
                    $data['name'],
                    $data['slug']
                );
            }

            return $tags;
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
                return new TagModel(
                    (int) $data['id'],
                    $data['name'],
                    $data['slug']
                );
            }

            return null;
        } catch (\PDOException $e) {
            echo $e->getMessage();
        }
    }
}
