<?php

declare(strict_types=1);

namespace App\Manager;

use App\Config\DatabaseConnexion;
use App\Model\CommentModel;
use App\Model\PostModel;
use App\Model\UserModel;

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

    public function find(int $id): ?CommentModel
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
            $statement = $this->db->prepare($sql);
            $statement->bindValue(':id', $id, \PDO::PARAM_INT);
            $statement->execute();
            if ($data = $statement->fetch(\PDO::FETCH_ASSOC)) {
                return $this->createCommentModelFromArray($data);
            }
        } catch (\PDOException $e) {
            error_log('Error fetching comment: '.$e->getMessage());
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
            $statement = $this->db->prepare($sql);
            $statement->execute(['value' => $value]);
            $data = $statement->fetch(\PDO::FETCH_ASSOC);
            if ($data) {
                return $this->createCommentModelFromArray($data);
            }
        } catch (\PDOException $e) {
            error_log('Error fetching comment: '.$e->getMessage());

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

            $statement = $this->db->prepare($sql);
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
        } catch (\PDOException $e) {
            echo $e->getMessage();
        }
    }

    public function countUserComments(int $userId): int
    {
        try {
            $sql = 'SELECT COUNT(*) FROM comment WHERE author_id = :user';

            $statement = $this->db->prepare($sql);
            $statement->execute(['user' => $userId]);

            return (int) $statement->fetchColumn();
        } catch (\PDOException $e) {
            echo $e->getMessage();
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

            $statement = $this->db->prepare($sql);
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
        } catch (\PDOException $e) {
            echo $e->getMessage();
        }
    }

    public function countPostComments(int $postId): int
    {
        try {
            $sql = 'SELECT COUNT(*) FROM comment WHERE post = :post';

            $statement = $this->db->prepare($sql);
            $statement->execute(['post' => $postId]);

            return (int) $statement->fetchColumn();
        } catch (\PDOException $e) {
            echo $e->getMessage();
        }
    }

    public function findByPage(int $postId, int $page, int $limit): array
    {
        try {
            $sql = 'SELECT * FROM comment WHERE post = :post ORDER BY created_at DESC LIMIT :limit OFFSET :offset';

            $statement = $this->db->prepare($sql);
            $statement->bindValue('post', $postId, \PDO::PARAM_INT);
            $statement->bindValue('limit', $limit, \PDO::PARAM_INT);
            $statement->bindValue('offset', ($page - 1) * $limit, \PDO::PARAM_INT);
            $statement->execute();

            $comments = [];

            while ($data = $statement->fetch(\PDO::FETCH_ASSOC)) {
                $comments[] = $this->createCommentModelFromArray($data);
            }

            return $comments;
        } catch (\PDOException $e) {
            echo $e->getMessage();
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

            $statement = $this->db->prepare($sql);
            $statement->execute(['post' => $postId]);
            $comments = [];
            while ($data = $statement->fetch(\PDO::FETCH_ASSOC)) {
                $comment = $this->createCommentModelFromArray($data);
                $comments[] = $comment;
            }

            return $comments;
        } catch (\PDOException $e) {
            echo $e->getMessage();
        }
    }

    public function create(array $commentData): void
    {
        try {
            $sql = 'INSERT INTO comment (created_at, content, author_id, is_enabled, parent_id, post_id) VALUES (:created_at, :content, :author_id, :is_enabled, :parent_id, :post_id)';
            $statement = $this->db->prepare($sql);
            $statement->execute([
                'created_at' => $commentData['created_at'],
                'content' => $commentData['content'],
                'author_id' => $commentData['author_id'],
                'is_enabled' => $commentData['is_enabled'],
                'parent_id' => $commentData['parent_id'],
                'post_id' => $commentData['post_id'],
            ]);
        } catch (\PDOException $e) {
            echo $e->getMessage();
        }
    }

    public function updateIsEnabled(CommentModel $comment): bool
    {
        try {
            $sql = 'UPDATE comment SET is_enabled = :isEnabled WHERE id = :id';
            $statement = $this->db->prepare($sql);
            $statement->bindValue(':isEnabled', $comment->getIsEnabled(), \PDO::PARAM_BOOL);
            $statement->bindValue(':id', $comment->getId(), \PDO::PARAM_INT);
            $statement->execute();

            return true;
        } catch (\PDOException $e) {
            error_log('Error updating comment status: '.$e->getMessage());

            return false;
        }
    }

    private function createCommentModelFromArray(array $data): CommentModel
    {
        $data['tags'] = isset($data['tags']) ? array_map('trim', explode(',', $data['tags'])) : [];
        $data['comments'] = isset($data['comments']) ? $data['comments'] : [];
        $data['category'] = isset($data['categories']) ? $data['categories'] : null;
        $author = new UserModel(
            (int) $data['author_id'],
            $data['username'],
            $data['email'],
            $data['password'],
            $data['created_at'],
            $data['role'],
            $data['avatar'],
            $data['bio'],
            $data['remember_me_token'],
            $data['remember_me_expires_at'],
            $data['firstName'],
            $data['lastName'],
            $data['twitter'],
            $data['facebook'],
            $data['linkedin'],
            $data['github'],
        );

        $post = new PostModel(
            (int) ($data['post_id'] ?? $data['id']),
            $data['title'],
            $data['content'],
            $data['chapo'],
            $data['created_at'],
            $data['updated_at'],
            (bool) $data['is_enabled'],
            $data['featured_image'],
            $author,
            $data['category'],
            $data['slug'],
            $data['tags'],
            $data['comments'],
        );

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
