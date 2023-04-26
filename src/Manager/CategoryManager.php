<?php

declare(strict_types=1);

namespace App\Manager;

use App\Config\DatabaseConnexion;
use App\Model\CategoryModel;

class CategoryManager
{
    private $database;

    public function __construct(DatabaseConnexion $databaseConnexion)
    {
        $this->database = $databaseConnexion->connect();
    }

    public function getDatabase()
    {
        return $this->database;
    }

    public function find(int $categoryId): ?CategoryModel
    {
        try {
            $sql = 'SELECT * FROM category WHERE id = :id';
            $statement = $this->database->prepare($sql);
            $statement->execute(['id' => $categoryId]);
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

    public function findOneBy(string $field, string $value): ?CategoryModel
    {
        try {
            $sql = "SELECT * FROM category WHERE {$field} = :value";
            $statement = $this->database->prepare($sql);
            $statement->execute(['value' => $value]);
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

    public function findAll(): array
    {
        try {
            $sql = 'SELECT * FROM category';
            $statement = $this->database->prepare($sql);
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

    public function findByPopularity(): array
    {
        try {
            $sql = 'SELECT category.id, category.name, category.slug, COUNT(post.id) AS nb_posts
                    FROM category
                    LEFT JOIN post ON post.category_id = category.id
                    GROUP BY category.id
                    ORDER BY nb_posts DESC';
            $statement = $this->database->prepare($sql);
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

    public function count(): int
    {
        try {
            $sql = 'SELECT COUNT(id) AS nb_categories FROM category';
            $statement = $this->database->prepare($sql);
            $statement->execute();
            $data = $statement->fetch(\PDO::FETCH_ASSOC);
            if ($data) {
                return (int) $data['nb_categories'];
            }
        } catch (\PDOException $e) {
            echo $e->getMessage();
        }
    }

    public function countPostsInCategory(int $postId): int
    {
        try {
            $sql = 'SELECT COUNT(post.id) AS nb_posts
                    FROM category
                    LEFT JOIN post ON post.category_id = category.id
                    WHERE category.id = :id';
            $statement = $this->database->prepare($sql);
            $statement->execute(['id' => $postId]);
            if ($data = $statement->fetch(\PDO::FETCH_ASSOC)) {
                return (int) $data['nb_posts'];
            }
        } catch (\PDOException $e) {
            echo $e->getMessage();
        }
    }

    public function create(CategoryModel $category): bool
    {
        try {
            $sql = 'INSERT INTO category (name, slug) VALUES (:name, :slug)';
            $statement = $this->database->prepare($sql);
            $statement->execute([
                'name' => $category->getName(),
                'slug' => $category->getSlug(),
            ]);

            return true;
        } catch (\PDOException $e) {
            echo $e->getMessage();
        }
    }

    public function update(CategoryModel $category): bool
    {
        try {
            $sql = 'UPDATE category SET name = :name, slug = :slug WHERE id = :id';
            $statement = $this->database->prepare($sql);
            $statement->execute([
                'id' => $category->getId(),
                'name' => $category->getName(),
                'slug' => $category->getSlug(),
            ]);

            return true;
        } catch (\PDOException $e) {
            echo $e->getMessage();
        }
    }

    public function delete(int $id): bool
    {
        try {
            $sql = 'DELETE FROM category WHERE id = :id';
            $statement = $this->database->prepare($sql);
            $statement->execute(['id' => $id]);

            return true;
        } catch (\PDOException $e) {
            echo $e->getMessage();
        }
    }
}
