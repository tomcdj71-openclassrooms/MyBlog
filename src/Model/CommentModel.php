<?php

declare(strict_types=1);

namespace App\Model;

use App\Config\DatabaseConnexion;
use App\Entity\Comment;

class CommentModel
{
    private $db;

    public function __construct(DatabaseConnexion $databaseConnexion)
    {
        $this->db = $databaseConnexion->connect();
    }

    public function findAll(): array
    {
        $stmt = $this->db->prepare('SELECT * FROM comment');
        $stmt->execute();

        return $stmt->fetchAll(\PDO::FETCH_CLASS, Comment::class);
    }

    public function find(int $id): ?Comment
    {
        $stmt = $this->db->prepare('SELECT * FROM comment WHERE id = :id');
        $stmt->bindValue(':id', $id, \PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchObject(Comment::class);
    }

    public function create(Comment $comment): bool
    {
        $stmt = $this->db->prepare('
            INSERT INTO comment (author, content, post_id, created_at)
            VALUES (:author, :content, :post_id, :created_at)
        ');

        $stmt->bindValue(':author', $comment->getAuthor());
        $stmt->bindValue(':content', $comment->getContent());
        $stmt->bindValue(':post_id', $comment->getPost(), \PDO::PARAM_INT);
        $stmt->bindValue(':created_at', $comment->getCreatedAt()->format('Y-m-d H:i:s'));

        return $stmt->execute();
    }

    public function update(Comment $comment): bool
    {
        $stmt = $this->db->prepare('
            UPDATE comment
            SET author = :author, content = :content, post_id = :post_id, created_at = :created_at
            WHERE id = :id
        ');

        $stmt->bindValue(':author', $comment->getAuthor());
        $stmt->bindValue(':content', $comment->getContent());
        $stmt->bindValue(':post_id', $comment->getPost(), \PDO::PARAM_INT);
        $stmt->bindValue(':created_at', $comment->getCreatedAt()->format('Y-m-d H:i:s'));
        $stmt->bindValue(':id', $comment->getId(), \PDO::PARAM_INT);

        return $stmt->execute();
    }

    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare('DELETE FROM comment WHERE id = :id');
        $stmt->bindValue(':id', $id, \PDO::PARAM_INT);

        return $stmt->execute();
    }
}
