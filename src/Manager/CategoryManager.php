<?php

declare(strict_types=1);

namespace App\Manager;

use App\Config\DatabaseConnexion;
use App\Model\CategoryModel;

class CategoryManager
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
            $sql = 'SELECT * FROM category';

            $statement = $this->db->prepare($sql);
            $statement->execute();

            $categories = [];
            while ($data = $statement->fetch(\PDO::FETCH_ASSOC)) {
                $categories[] = new CategoryModel(
                    (int) $data['id'],
                    $data['name'],
                    $data['slug']
                );
            }

            return $categories;
        } catch (\PDOException $e) {
            echo $e->getMessage();
        }
    }

    public function findBySlug(string $slug): ?CategoryModel
    {
        try {
            $sql = 'SELECT * FROM category WHERE slug = :slug';

            $statement = $this->db->prepare($sql);
            $statement->execute(['slug' => $slug]);

            if ($data = $statement->fetch(\PDO::FETCH_ASSOC)) {
                return new CategoryModel(
                    (int) $data['id'],
                    $data['name'],
                    $data['slug']
                );
            }
        } catch (\PDOException $e) {
            echo $e->getMessage();
        }
    }

    public function find(int $id): ?CategoryModel
    {
        try {
            $sql = 'SELECT * FROM category WHERE id = :id';

            $statement = $this->db->prepare($sql);
            $statement->execute(['id' => $id]);

            if ($data = $statement->fetch(\PDO::FETCH_ASSOC)) {
                return new CategoryModel(
                    (int) $data['id'],
                    $data['name'],
                    $data['slug']
                );
            }
        } catch (\PDOException $e) {
            echo $e->getMessage();
        }
    }

    public function findPopularCategories(): array
    {
        try {
            $sql = 'SELECT category.id, category.name, category.slug, COUNT(post.id) AS nb_posts
                    FROM category
                    LEFT JOIN post ON post.category_id = category.id
                    GROUP BY category.id
                    ORDER BY nb_posts DESC
                    LIMIT 5';

            $statement = $this->db->prepare($sql);
            $statement->execute();

            $categories = [];
            while ($data = $statement->fetch(\PDO::FETCH_ASSOC)) {
                $categories[] = new CategoryModel(
                    (int) $data['id'],
                    $data['name'],
                    $data['slug'],
                    (int) $data['nb_posts']
                );
            }

            return $categories;
        } catch (\PDOException $e) {
            echo $e->getMessage();
        }
    }

    public function countPostsInCategory(int $id): int
    {
        try {
            $sql = 'SELECT COUNT(post.id) AS nb_posts
                    FROM category
                    LEFT JOIN post ON post.category_id = category.id
                    WHERE category.id = :id';

            $statement = $this->db->prepare($sql);
            $statement->execute(['id' => $id]);

            if ($data = $statement->fetch(\PDO::FETCH_ASSOC)) {
                return (int) $data['nb_posts'];
            }
        } catch (\PDOException $e) {
            echo $e->getMessage();
        }
    }
}
