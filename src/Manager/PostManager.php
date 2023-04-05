<?php

declare(strict_types=1);

namespace App\Manager;

use App\Config\DatabaseConnexion;
use App\Model\PostModel;

class PostManager
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

    public function findBySlug(string $slug): ?PostModel
    {
        try {
            $sql = 'SELECT p.*, u.username as author_name, c.name as category_name, c.slug as category_slug, GROUP_CONCAT(t.name) as tag_names
            FROM post p
            LEFT JOIN user u ON p.author_id = u.id
            LEFT JOIN category c ON p.category_id = c.id
            LEFT JOIN tag t ON instr("," || p.tags || ",", "," || t.id || ",") > 0
            WHERE p.slug = :slug
            GROUP BY p.id';

            $statement = $this->db->prepare($sql);
            $statement->execute(['slug' => $slug]);

            if ($data = $statement->fetch(\PDO::FETCH_ASSOC)) {
                $tags = array_map(function ($tag) {
                    return ['name' => $tag,
                        'slug' => strtolower(str_replace(' ', '-', $tag))];
                }, explode(',', $data['tag_names']));

                return new PostModel(
                    (int) $data['id'],
                    $data['title'],
                    $data['content'],
                    $data['chapo'],
                    $data['created_at'],
                    $data['updated_at'],
                    (bool) $data['is_enabled'],
                    $data['featured_image'],
                    $data['author_name'],
                    $data['category_name'],
                    $data['slug'],
                    $data['category_slug'],
                    $tags
                );
            }

            return null;
        } catch (\PDOException $e) {
            return 'Error: '.$e->getMessage();
        }
    }

    public function findAll(): array
    {
        try {
            $sql = 'SELECT p.*, u.username as author_name, c.name as category_name, c.slug as category_slug, GROUP_CONCAT(t.name) as tag_names
            FROM post p
            LEFT JOIN user u ON p.author_id = u.id
            LEFT JOIN category c ON p.category_id = c.id
            LEFT JOIN tag t ON instr("," || p.tags || ",", "," || t.id || ",") > 0
            GROUP BY p.id';

            $statement = $this->db->prepare($sql);
            $statement->execute();

            $posts = [];
            while ($data = $statement->fetch(\PDO::FETCH_ASSOC)) {
                $tags = array_map(function ($tag) {
                    return ['name' => $tag,
                        'slug' => strtolower(str_replace(' ', '-', $tag))];
                }, explode(',', $data['tag_names']));

                $posts[] = new PostModel(
                    (int) $data['id'],
                    $data['title'],
                    $data['content'],
                    $data['chapo'],
                    $data['created_at'],
                    $data['updated_at'],
                    (bool) $data['is_enabled'],
                    $data['featured_image'],
                    $data['author_name'],
                    $data['category_name'],
                    $data['slug'],
                    $data['category_slug'],
                    $tags
                );
            }

            return $posts;
        } catch (\PDOException $e) {
            return 'Error: '.$e->getMessage();
        }
    }

    public function find(int $id): ?PostModel
    {
        try {
            $sql = 'SELECT p.*, u.username as author_name, c.name as category_name, c.slug as category_slug, GROUP_CONCAT(t.name) as tag_names
            FROM post p
            LEFT JOIN user u ON p.author_id = u.id
            LEFT JOIN category c ON p.category_id = c.id
            LEFT JOIN tag t ON instr("," || p.tags || ",", "," || t.id || ",") > 0
            WHERE p.id = :id
            GROUP BY p.id';

            $statement = $this->db->prepare($sql);
            $statement->execute(['id' => $id]);

            if ($data = $statement->fetch(\PDO::FETCH_ASSOC)) {
                $tags = array_map(function ($tag) {
                    return ['name' => $tag,
                        'slug' => strtolower(str_replace(' ', '-', $tag))];
                }, explode(',', $data['tag_names']));

                return new PostModel(
                    (int) $data['id'],
                    $data['title'],
                    $data['content'],
                    $data['chapo'],
                    $data['created_at'],
                    $data['updated_at'],
                    (bool) $data['is_enabled'],
                    $data['featured_image'],
                    $data['author_name'],
                    $data['category_name'],
                    $data['slug'],
                    $data['category_slug'],
                    $tags
                );
            }

            return null;
        } catch (\PDOException $e) {
            return 'Error: '.$e->getMessage();
        }
    }

    public function findPostsWithTag(string $slug): array
    {
        try {
            $sql = 'SELECT p.*, u.username as author_name, c.name as category_name, c.slug as category_slug, GROUP_CONCAT(t.name) as tag_names
            FROM post p
            LEFT JOIN user u ON p.author_id = u.id
            LEFT JOIN category c ON p.category_id = c.id
            LEFT JOIN tag t ON instr("," || p.tags || ",", "," || t.id || ",") > 0
            WHERE instr("," || p.tags || ",", "," || (SELECT id FROM tag WHERE slug = :slug) || ",") > 0
            GROUP BY p.id';

            $statement = $this->db->prepare($sql);
            $statement->execute(['slug' => $slug]);

            $posts = [];
            while ($data = $statement->fetch(\PDO::FETCH_ASSOC)) {
                $tags = array_map(function ($tag) {
                    return ['name' => $tag,
                        'slug' => strtolower(str_replace(' ', '-', $tag))];
                }, explode(',', $data['tag_names']));

                $posts[] = new PostModel(
                    (int) $data['id'],
                    $data['title'],
                    $data['content'],
                    $data['chapo'],
                    $data['created_at'],
                    $data['updated_at'],
                    (bool) $data['is_enabled'],
                    $data['featured_image'],
                    $data['author_name'],
                    $data['category_name'],
                    $data['slug'],
                    $data['category_slug'],
                    $tags
                );
            }

            return $posts;
        } catch (\PDOException $e) {
            echo $e->getMessage();
        }
    }

    public function findPostsWithCategory(string $slug): array
    {
        try {
            $sql = 'SELECT p.*, u.username as author_name, c.name as category_name, c.slug as category_slug, GROUP_CONCAT(t.name) as tag_names
            FROM post p
            LEFT JOIN user u ON p.author_id = u.id
            LEFT JOIN category c ON p.category_id = c.id
            LEFT JOIN tag t ON instr("," || p.tags || ",", "," || t.id || ",") > 0
            WHERE c.slug = :slug
            GROUP BY p.id';

            $statement = $this->db->prepare($sql);
            $statement->execute(['slug' => $slug]);

            $posts = [];
            while ($data = $statement->fetch(\PDO::FETCH_ASSOC)) {
                $tags = array_map(function ($tag) {
                    return ['name' => $tag,
                        'slug' => strtolower(str_replace(' ', '-', $tag))];
                }, explode(',', $data['tag_names']));

                $posts[] = new PostModel(
                    (int) $data['id'],
                    $data['title'],
                    $data['content'],
                    $data['chapo'],
                    $data['created_at'],
                    $data['updated_at'],
                    (bool) $data['is_enabled'],
                    $data['featured_image'],
                    $data['author_name'],
                    $data['category_name'],
                    $data['slug'],
                    $data['category_slug'],
                    $tags
                );
            }

            return $posts;
        } catch (\PDOException $e) {
            echo $e->getMessage();
        }
    }

    public function findRecentPosts(int $limit = 5): array
    {
        try {
            $sql = 'SELECT p.*, u.username as author_name, c.name as category_name, c.slug as category_slug, GROUP_CONCAT(t.name) as tag_names
            FROM post p
            LEFT JOIN user u ON p.author_id = u.id
            LEFT JOIN category c ON p.category_id = c.id
            LEFT JOIN tag t ON instr("," || p.tags || ",", "," || t.id || ",") > 0
            WHERE p.is_enabled = 1
            GROUP BY p.id
            ORDER BY p.created_at DESC
            LIMIT :limit';

            $statement = $this->db->prepare($sql);
            $statement->execute(['limit' => $limit]);

            $posts = [];
            while ($data = $statement->fetch(\PDO::FETCH_ASSOC)) {
                $tags = array_map(function ($tag) {
                    return ['name' => $tag,
                        'slug' => strtolower(str_replace(' ', '-', $tag))];
                }, explode(',', $data['tag_names']));

                $posts[] = new PostModel(
                    (int) $data['id'],
                    $data['title'],
                    $data['content'],
                    $data['chapo'],
                    $data['created_at'],
                    $data['updated_at'],
                    (bool) $data['is_enabled'],
                    $data['featured_image'],
                    $data['author_name'],
                    $data['category_name'],
                    $data['slug'],
                    $data['category_slug'],
                    $tags
                );
            }

            return $posts;
        } catch (\PDOException $e) {
            echo $e->getMessage();
        }
    }

    public function findPostsPostedAt(string $date): array
    {
        try {
            $sql = 'SELECT p.*, u.username as author_name, c.name as category_name, c.slug as category_slug, GROUP_CONCAT(t.name) as tag_names
            FROM post p
            LEFT JOIN user u ON p.author_id = u.id
            LEFT JOIN category c ON p.category_id = c.id
            LEFT JOIN tag t ON instr("," || p.tags || ",", "," || t.id || ",") > 0
            WHERE p.is_enabled = 1 AND p.created_at LIKE :date
            GROUP BY p.id
            ORDER BY p.created_at DESC';

            $statement = $this->db->prepare($sql);
            $statement->execute(['date' => $date.'%']);

            $posts = [];
            while ($data = $statement->fetch(\PDO::FETCH_ASSOC)) {
                $tags = array_map(function ($tag) {
                    return ['name' => $tag,
                        'slug' => strtolower(str_replace(' ', '-', $tag))];
                }, explode(',', $data['tag_names']));

                $posts[] = new PostModel(
                    (int) $data['id'],
                    $data['title'],
                    $data['content'],
                    $data['chapo'],
                    $data['created_at'],
                    $data['updated_at'],
                    (bool) $data['is_enabled'],
                    $data['featured_image'],
                    $data['author_name'],
                    $data['category_name'],
                    $data['slug'],
                    $data['category_slug'],
                    $tags
                );
            }

            return $posts;
        } catch (\PDOException $e) {
            echo $e->getMessage();
        }
    }

    public function findPostsWithAuthor(string $username): array
    {
        try {
            $sql = 'SELECT p.*, u.username as author_name, c.name as category_name, c.slug as category_slug, GROUP_CONCAT(t.name) as tag_names
            FROM post p
            LEFT JOIN user u ON p.author_id = u.id
            LEFT JOIN category c ON p.category_id = c.id
            LEFT JOIN tag t ON instr("," || p.tags || ",", "," || t.id || ",") > 0
            WHERE u.username = :username
            GROUP BY p.id';

            $statement = $this->db->prepare($sql);
            $statement->execute(['username' => $username]);

            $posts = [];
            while ($data = $statement->fetch(\PDO::FETCH_ASSOC)) {
                $tags = array_map(function ($tag) {
                    return ['name' => $tag,
                        'slug' => strtolower(str_replace(' ', '-', $tag))];
                }, explode(',', $data['tag_names']));

                $posts[] = new PostModel(
                    (int) $data['id'],
                    $data['title'],
                    $data['content'],
                    $data['chapo'],
                    $data['created_at'],
                    $data['updated_at'],
                    (bool) $data['is_enabled'],
                    $data['featured_image'],
                    $data['author_name'],
                    $data['category_name'],
                    $data['slug'],
                    $data['category_slug'],
                    $tags
                );
            }

            return $posts;
        } catch (\PDOException $e) {
            echo $e->getMessage();
        }
    }

    public function create(PostModel $post): void
    {
        try {
            $sql = 'INSERT INTO post (title, content, chapo, created_at, updated_at, is_enabled, featured_image, author_id, category_id, tags, slug)
            VALUES (:title, :content, :chapo, :created_at, :updated_at, :is_enabled, :featured_image, :author_id, :category_id, :tags, :slug)';

            $statement = $this->db->prepare($sql);
            $statement->execute([
                'title' => $post->getTitle(),
                'content' => $post->getContent(),
                'chapo' => $post->getChapo(),
                'created_at' => $post->getCreatedAt(),
                'updated_at' => $post->getUpdatedAt(),
                'is_enabled' => $post->getIsEnabled(),
                'featured_image' => $post->getFeaturedImage(),
                'author_id' => $post->getAuthor(),
                'category_id' => $post->getCategory(),
                'tags' => $post->getTags(),
                'slug' => $post->getSlug(),
            ]);
        } catch (\PDOException $e) {
            error_log('Error creating post: '.$e->getMessage());
        }
    }

    public function update(PostModel $post): void
    {
        try {
            $sql = 'UPDATE post
            SET title = :title, content = :content, chapo = :chapo, created_at = :created_at, updated_at = :updated_at, is_enabled = :is_enabled, featured_image = :featured_image, author_id = :author_id, category_id = :category_id, tags = :tags, slug = :slug
            WHERE id = :id';

            $statement = $this->db->prepare($sql);
            $statement->execute([
                'id' => $post->getId(),
                'title' => $post->getTitle(),
                'content' => $post->getContent(),
                'chapo' => $post->getChapo(),
                'created_at' => $post->getCreatedAt(),
                'updated_at' => $post->getUpdatedAt(),
                'is_enabled' => $post->getIsEnabled(),
                'featured_image' => $post->getFeaturedImage(),
                'author_id' => $post->getAuthor(),
                'category_id' => $post->getCategory(),
                'tags' => $post->getTags(),
                'slug' => $post->getSlug(),
            ]);
        } catch (\PDOException $e) {
            error_log('Error updating post: '.$e->getMessage());
        }
    }

    public function delete(int $id): bool
    {
        try {
            $sql = 'DELETE FROM post WHERE id = :id';

            $statement = $this->db->prepare($sql);

            return $statement->execute(['id' => $id]);
        } catch (\PDOException $e) {
            error_log('Error deleting post: '.$e->getMessage());

            return false;
        }
    }
}
