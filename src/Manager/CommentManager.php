<?php

declare(strict_types=1);

namespace App\Manager;

use App\Config\DatabaseConnexion;
use App\Model\CommentModel;
use App\Model\PostModel;
use App\Model\UserModel;
use App\ModelParameters\PostModelParameters;
use App\ModelParameters\UserModelParameters;

class CommentManager
{
    private $database;
    private PostModelParameters $postModelParams;
    private UserModelParameters $userModelParams;

    public function __construct(DatabaseConnexion $databaseConnexion)
    {
        $this->database = $databaseConnexion->connect();
        $this->postModelParams = new PostModelParameters();
        $this->userModelParams = new UserModelParameters();
    }

    public function getDatabase()
    {
        return $this->database;
    }

    public function find(int $commentId): ?CommentModel
    {
        try {
            $sql = 'SELECT comment.id, comment.content, comment.author_id, comment.post_id, comment.created_at, comment.is_enabled, comment.parent_id,
                    user.id AS author_id, user.username, user.email, user.password, user.role, user.firstName, user.lastName, user.avatar, user.bio, user.twitter, user.facebook, user.github, user.linkedin, user.remember_me_token, user.remember_me_expires_at,
                    post.title, post.chapo, post.updated_at, post.featured_image, post.category_id, post.slug, post.tags
                FROM comment 
                INNER JOIN user ON comment.author_id = user.id 
                INNER JOIN post ON comment.post_id = post.id 
                WHERE comment.id = :id
                LIMIT 1';
            $statement = $this->database->prepare($sql);
            $statement->bindValue(':id', $commentId, \PDO::PARAM_INT);
            $statement->execute();
            $data = $statement->fetch(\PDO::FETCH_ASSOC);
            if ($data) {
                return $this->createCommentModelFromArray($data);
            }
        } catch (\PDOException $error) {
            throw new \PDOException($error->getMessage(), (int) $error->getCode());
        }
    }

    public function findOneBy(string $field, string $value): ?CommentModel
    {
        try {
            $sql = 'SELECT comment.id, comment.content, comment.author_id, comment.post_id, comment.created_at, comment.is_enabled, comment.parent_id,
                    user.id AS author_id, user.username, user.email, user.password, user.role, user.firstName, user.lastName, user.avatar, user.bio, user.twitter, user.facebook, user.github, user.linkedin, user.remember_me_token, user.remember_me_expires_at,
                    post.title, post.chapo, post.updated_at, post.featured_image, post.category_id, post.slug, post.tags
                FROM comment 
                INNER JOIN user ON comment.author_id = user.id 
                INNER JOIN post ON comment.post_id = post.id 
                WHERE {$field} = :value';
            $statement = $this->database->prepare($sql);
            $statement->execute(['value' => $value]);
            $data = $statement->fetch(\PDO::FETCH_ASSOC);
            if ($data) {
                return $this->createCommentModelFromArray($data);
            }
        } catch (\PDOException $error) {
            throw new \PDOException($error->getMessage(), (int) $error->getCode());

            return null;
        }
    }

    public function findUserComments(int $userId, int $page, int $limit): array
    {
        try {
            $sql = 'SELECT comment.id, comment.content, comment.author_id, comment.post_id, comment.created_at, comment.is_enabled, comment.parent_id,
                    user.id AS author_id, user.username, user.email, user.password, user.role, user.firstName, user.lastName, user.avatar, user.bio, user.twitter, user.facebook, user.github, user.linkedin, user.remember_me_token, user.remember_me_expires_at,
                    post.title, post.chapo, post.updated_at, post.featured_image, post.category_id, post.slug, post.tags
                FROM comment 
                INNER JOIN user ON comment.author_id = user.id 
                INNER JOIN post ON comment.post_id = post.id 
                WHERE comment.author_id = :user
                ORDER BY comment.created_at DESC
                LIMIT :limit OFFSET :offset';
            $offset = ($page - 1) * $limit;
            $statement = $this->database->prepare($sql);
            $statement->bindValue(':user', $userId, \PDO::PARAM_INT);
            $statement->bindValue(':limit', $limit, \PDO::PARAM_INT);
            $statement->bindValue(':offset', $offset, \PDO::PARAM_INT);
            $statement->execute();
            $comments = [];
            while ($data = $statement->fetch(\PDO::FETCH_ASSOC)) {
                $comment = $this->createCommentModelFromArray($data);
                $comments[] = $comment;
            }

            return $comments;
        } catch (\PDOException $error) {
            throw new \PDOException($error->getMessage(), (int) $error->getCode());
        }
    }

    public function countUserComments(int $userId): int
    {
        try {
            $sql = 'SELECT COUNT(*) FROM comment WHERE author_id = :user';
            $statement = $this->database->prepare($sql);
            $statement->execute(['user' => $userId]);

            return (int) $statement->fetchColumn();
        } catch (\PDOException $error) {
            throw new \PDOException($error->getMessage(), (int) $error->getCode());
        }
    }

    public function findAll(int $page = 1, int $limit = 10): array
    {
        try {
            $sql = 'SELECT comment.*, (SELECT COUNT(*) FROM comment) as total_comments,
                user.id AS author_id, user.username, user.email, user.password, user.role, user.firstName, user.lastName, user.avatar, user.bio, user.twitter, user.facebook, user.github, user.linkedin, user.remember_me_token, user.remember_me_expires_at,
                post.id AS post_id, post.title, post.chapo, post.updated_at, post.featured_image, post.category_id, post.slug, post.tags
                FROM comment
                INNER JOIN user ON comment.author_id = user.id
                INNER JOIN post ON comment.post_id = post.id
                ORDER BY comment.created_at DESC
                LIMIT :limit OFFSET :offset';
            $offset = ($page - 1) * $limit;
            $statement = $this->database->prepare($sql);
            $statement->bindValue(':limit', $limit, \PDO::PARAM_INT);
            $statement->bindValue(':offset', $offset, \PDO::PARAM_INT);
            $statement->execute();
            $comments = [];
            $totalComments = 0;
            while ($data = $statement->fetch(\PDO::FETCH_ASSOC)) {
                $comments[] = $this->createCommentModelFromArray($data);
                $totalComments = $data['total_comments'];
            }

            return ['comments' => $comments, 'total_comments' => $totalComments];
        } catch (\PDOException $error) {
            throw new \PDOException($error->getMessage(), (int) $error->getCode());
        }
    }

    public function countPostComments(int $postId): int
    {
        try {
            $sql = 'SELECT COUNT(*) FROM comment WHERE post = :post';

            $statement = $this->database->prepare($sql);
            $statement->execute(['post' => $postId]);

            return (int) $statement->fetchColumn();
        } catch (\PDOException $error) {
            throw new \PDOException($error->getMessage(), (int) $error->getCode());
        }
    }

    public function findByPage(int $postId, int $page, int $limit): array
    {
        try {
            $sql = 'SELECT * FROM comment WHERE post = :post ORDER BY created_at DESC LIMIT :limit OFFSET :offset';
            $statement = $this->database->prepare($sql);
            $statement->bindValue('post', $postId, \PDO::PARAM_INT);
            $statement->bindValue('limit', $limit, \PDO::PARAM_INT);
            $statement->bindValue('offset', ($page - 1) * $limit, \PDO::PARAM_INT);
            $statement->execute();
            $comments = [];
            while ($data = $statement->fetch(\PDO::FETCH_ASSOC)) {
                $comments[] = $this->createCommentModelFromArray($data);
            }

            return $comments;
        } catch (\PDOException $error) {
            throw new \PDOException($error->getMessage(), (int) $error->getCode());
        }
    }

    public function findAllByPost(int $postId): array
    {
        try {
            $sql = 'SELECT comment.id, comment.content, comment.author_id, comment.post_id, comment.created_at, comment.is_enabled, comment.parent_id,
                        user.id AS author_id, user.username, user.email, user.password, user.role, user.firstName, user.lastName, user.avatar, user.bio, user.twitter, user.facebook, user.github, user.linkedin, user.remember_me_token, user.remember_me_expires_at,
                        post.title, post.chapo, post.updated_at, post.featured_image, post.category_id, post.slug, post.tags
                    FROM comment 
                    INNER JOIN user ON comment.author_id = user.id 
                    INNER JOIN post ON comment.post_id = post.id 
                    WHERE comment.post_id = :post ORDER BY comment.created_at ASC';
            $statement = $this->database->prepare($sql);
            $statement->execute(['post' => $postId]);
            $comments = [];
            while ($data = $statement->fetch(\PDO::FETCH_ASSOC)) {
                $comment = $this->createCommentModelFromArray($data);
                $comments[] = $comment;
            }

            return $comments;
        } catch (\PDOException $error) {
            throw new \PDOException($error->getMessage(), (int) $error->getCode());
        }
    }

    public function create(array $commentData): void
    {
        try {
            $sql = 'INSERT INTO comment (created_at, content, author_id, is_enabled, parent_id, post_id) VALUES (:created_at, :content, :author_id, :is_enabled, :parent_id, :post_id)';
            $statement = $this->database->prepare($sql);
            $statement->execute([
                'created_at' => $commentData['created_at'],
                'content' => $commentData['content'],
                'author_id' => $commentData['author_id'],
                'is_enabled' => $commentData['is_enabled'],
                'parent_id' => $commentData['parent_id'],
                'post_id' => $commentData['post_id'],
            ]);
        } catch (\PDOException $error) {
            throw new \PDOException($error->getMessage(), (int) $error->getCode());
        }
    }

    public function updateIsEnabled(CommentModel $comment): bool
    {
        try {
            $sql = 'UPDATE comment SET is_enabled = :isEnabled WHERE id = :id';
            $statement = $this->database->prepare($sql);
            $statement->bindValue(':isEnabled', $comment->getIsEnabled(), \PDO::PARAM_BOOL);
            $statement->bindValue(':id', $comment->getId(), \PDO::PARAM_INT);
            $statement->execute();

            return true;
        } catch (\PDOException $error) {
            throw new \PDOException($error->getMessage(), (int) $error->getCode());
        }
    }

    private function createCommentModelFromArray(array $data): CommentModel
    {
        $data['tags'] = isset($data['tags']) ? array_map('trim', explode(',', $data['tags'])) : [];
        $data['comments'] = isset($data['comments']) ? $data['comments'] : [];
        $data['category'] = isset($data['categories']) ? $data['categories'] : null;
        $authorModelParams = $this->userModelParams->createFromData($data);
        $author = new UserModel($authorModelParams);
        $data['author'] = $author;
        $postModelParams = $this->postModelParams->createFromData($data);
        $post = new PostModel($postModelParams);

        return new CommentModel(
            (int) $data['id'],
            $data['content'],
            $data['created_at'],
            $author,
            (bool) $data['is_enabled'],
            $data['parent_id'],
            $post
        );
    }
}
