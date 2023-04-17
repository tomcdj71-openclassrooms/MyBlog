<?php

declare(strict_types=1);

namespace App\Manager;

use App\Config\DatabaseConnexion;
use App\Model\CommentModel;

class CommentManager
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

    public function findBy(array $params): array
    {
        try {
            $sql = 'SELECT * FROM comment WHERE 1=1';
            $sql .= isset($params['id']) ? ' AND id = :id' : '';
            $sql .= isset($params['post_id']) ? ' AND post_id = :post_id' : '';
            $sql .= isset($params['is_enabled']) ? ' AND is_enabled = :is_enabled' : '';
            $sql .= isset($params['parent_id']) ? ' AND parent_id = :parent_id' : '';
            $sql .= isset($params['order']) ? ' ORDER BY created_at '.$params['order'] : '';
            $sql .= isset($params['limit']) ? ' LIMIT '.$params['limit'] : '';

            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            $comments = $stmt->fetchAll();

            $commentModels = [];
            foreach ($comments as $comment) {
                $commentModels[] = $this->createCommentModelFromArray($comment);
            }

            return $commentModels;
        } catch (\PDOException $e) {
            echo 'Error: '.$e->getMessage();
        }
    }

    public function findAll(): array
    {
        try {
            $sql = 'SELECT * FROM comment';
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            $comments = $stmt->fetchAll();

            $commentModels = [];
            foreach ($comments as $comment) {
                $commentModels[] = $this->createCommentModelFromArray($comment);
            }

            return $commentModels;
        } catch (\PDOException $e) {
            echo 'Error: '.$e->getMessage();
        }
    }

    public function find(int $id): CommentModel
    {
        try {
            $sql = 'SELECT * FROM comment WHERE id = :id';
            $stmt = $this->db->prepare($sql);
            $stmt->execute(['id' => $id]);
            $comment = $stmt->fetch();

            return $this->createCommentModelFromArray($comment);
        } catch (\PDOException $e) {
            echo 'Error: '.$e->getMessage();
        }
    }

    public function create($data): CommentModel
    {
        if ($data instanceof CommentModel) {
            $commentModel = $data;
        } elseif (is_array($data)) {
            $commentModel = $this->createCommentModelFromArray($data);
        } else {
            throw new \InvalidArgumentException('$data must be an instance of CommentModel or an array.');
        }

        try {
            $sql = 'INSERT INTO comment (content, author_id, post_id, created_at, is_enabled, parent_id) 
                VALUES (:content, :author_id, :post_id, :created_at, :is_enabled, :parent_id)';
            $stmt = $this->db->prepare($sql);

            $is_enabled = ($commentModel->getIsEnabled()) ? 1 : 0;

            $stmt->bindValue(':content', $commentModel->getContent());
            $stmt->bindValue(':author_id', $commentModel->getAuthor());
            $stmt->bindValue(':post_id', $commentModel->getPostId());
            $stmt->bindValue(':created_at', $commentModel->getCreatedAt());
            $stmt->bindValue(':is_enabled', $is_enabled, \PDO::PARAM_INT); // Bind as integer
            $stmt->bindValue(':parent_id', $commentModel->getParentId());

            $stmt->execute();

            $commentModel->setId((int) $this->db->lastInsertId());

            return $commentModel;
        } catch (\PDOException $e) {
            echo 'Error: '.$e->getMessage();

            return null;
        }
    }

    private function createCommentModelFromArray(array $data): CommentModel
    {
        $id = isset($data['id']) ? (int) $data['id'] : 0;
        $author = isset($data['author_id']) ? (int) $data['author_id'] : null;
        $parentId = isset($data['parent_id']) ? (int) $data['parent_id'] : null;

        // if parent_id is null, set it to 0
        if (null === $parentId) {
            $parentId = 0;
        }

        return new CommentModel(
            $id,
            $data['content'],
            $data['created_at'],
            $author,
            (int) $data['post_id'],
            (bool) $data['is_enabled'],
            $parentId
        );
    }
}
