<?php

declare(strict_types=1);

namespace App\Manager;

use App\Config\DatabaseConnexion;

class PostTagManager
{
    private \PDO $database;

    public function __construct(DatabaseConnexion $databaseConnexion)
    {
        $this->database = $databaseConnexion->connect();
    }

    public function addPostTag(int $postId, int $tagId): void
    {
        try {
            $sql = 'INSERT INTO post_tag (post_id, tag_id) VALUES (:post_id, :tag_id)';
            $statement = $this->database->prepare($sql);
            $statement->bindValue(':post_id', $postId, \PDO::PARAM_INT);
            $statement->bindValue(':tag_id', $tagId, \PDO::PARAM_INT);
            $statement->execute();
        } catch (\PDOException $error) {
            throw new \PDOException($error->getMessage(), (int) $error->getCode());
        }
    }

    public function removePostTag(int $postId, int $tagId): void
    {
        try {
            $sql = 'DELETE FROM post_tag WHERE post_id = :post_id AND tag_id = :tag_id';
            $statement = $this->database->prepare($sql);
            $statement->bindValue(':post_id', $postId, \PDO::PARAM_INT);
            $statement->bindValue(':tag_id', $tagId, \PDO::PARAM_INT);
            $statement->execute();
        } catch (\PDOException $error) {
            throw new \PDOException($error->getMessage(), (int) $error->getCode());
        }
    }

    public function getPostTagsByPostId(int $postId): array
    {
        try {
            $sql = 'SELECT * FROM post_tag WHERE post_id = :post_id';
            $statement = $this->database->prepare($sql);
            $statement->bindValue(':post_id', $postId, \PDO::PARAM_INT);
            $statement->execute();
            $postTags = [];
            while ($data = $statement->fetch(\PDO::FETCH_ASSOC)) {
                $postTags[] = $data;
            }

            return $postTags;
        } catch (\PDOException $error) {
            throw new \PDOException($error->getMessage(), (int) $error->getCode());
        }
    }

    public function getPostTagsByTagId(int $tagId): array
    {
        try {
            $sql = 'SELECT * FROM post_tag WHERE tag_id = :tag_id';
            $statement = $this->database->prepare($sql);
            $statement->bindValue(':tag_id', $tagId, \PDO::PARAM_INT);
            $statement->execute();
            $postTags = [];
            while ($data = $statement->fetch(\PDO::FETCH_ASSOC)) {
                $postTags[] = $data;
            }

            return $postTags;
        } catch (\PDOException $error) {
            throw new \PDOException($error->getMessage(), (int) $error->getCode());
        }
    }
}
