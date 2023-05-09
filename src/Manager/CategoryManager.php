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

    public function find(int $id): ?CategoryModel
    {
        try {
            $sql = 'SELECT * FROM category WHERE id = :id';
            $statement = $this->database->prepare($sql);
            $statement->execute(['id' => $id]);
            $data = $statement->fetch(\PDO::FETCH_ASSOC);
            if (!$data) {
                return null;
            }

            return new CategoryModel(
                (int) $data['id'],
                $data['name'],
                $data['slug']
            );
        } catch (\PDOException $error) {
            throw new \PDOException($error->getMessage(), (int) $error->getCode());
        }
    }

    public function findAll(): array
    {
        try {
            $sql = 'SELECT category.*, COUNT(post.id) as post_count 
                FROM category 
                LEFT JOIN post ON category.id = post.category_id 
                GROUP BY category.id
                ORDER BY category.name ASC';
            $statement = $this->database->prepare($sql);
            $statement->execute();
            $categories = [];
            while ($data = $statement->fetch(\PDO::FETCH_ASSOC)) {
                $category = new CategoryModel(
                    (int) $data['id'],
                    $data['name'],
                    $data['slug']
                );
                $category->setNbPosts((int) $data['post_count']);
                $categories[] = $category;
            }

            return $categories;
        } catch (\PDOException $error) {
            throw new \PDOException($error->getMessage(), (int) $error->getCode());
        }
    }

    public function findByPopularity(): array
    {
        try {
            $sql = 'SELECT category.id, category.name, category.slug, COUNT(post.id) AS nb_posts
                FROM category
                LEFT JOIN post ON post.category_id = category.id AND post.enabled = 1
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
        } catch (\PDOException $error) {
            throw new \PDOException($error->getMessage(), (int) $error->getCode());
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
        } catch (\PDOException $error) {
            throw new \PDOException($error->getMessage(), (int) $error->getCode());
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
            $data = $statement->fetch(\PDO::FETCH_ASSOC);
            if ($data) {
                return (int) $data['nb_posts'];
            }
        } catch (\PDOException $error) {
            throw new \PDOException($error->getMessage(), (int) $error->getCode());
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
        } catch (\PDOException $error) {
            throw new \PDOException($error->getMessage(), (int) $error->getCode());
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
        } catch (\PDOException $error) {
            throw new \PDOException($error->getMessage(), (int) $error->getCode());
        }
    }

    public function delete(int $id): bool
    {
        try {
            $sql = 'DELETE FROM category WHERE id = :id';
            $statement = $this->database->prepare($sql);
            $statement->execute(['id' => $id]);

            return true;
        } catch (\PDOException $error) {
            throw new \PDOException($error->getMessage(), (int) $error->getCode());
        }
    }
}
