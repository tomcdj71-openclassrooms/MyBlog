<?php

declare(strict_types=1);

namespace App\Manager;

use App\Config\DatabaseConnexion;
use App\Factory\ModelFactory;
use App\Model\CategoryModel;
use App\Model\CommentModel;
use App\Model\PostModel;
use App\Model\TagModel;
use App\Model\UserModel;

class PostManager
{
    private \PDO $database;
    private ModelFactory $modelFactory;

    public function __construct(DatabaseConnexion $databaseConnexion)
    {
        $this->database = $databaseConnexion->connect();
        $this->modelFactory = new ModelFactory();
    }

    public function find(int $id): ?PostModel
    {
        try {
            $sql = 'SELECT p.id as post_id, p.title, p.author_id, p.content, p.chapo, p.created_at, p.updated_at, p.is_enabled, p.featured_image, p.category_id, p.slug,
                u.id as user_id, u.*,
                c.id as category_id, c.name as category_name, c.slug as category_slug,
                GROUP_CONCAT(DISTINCT t.name) as tag_names,
                GROUP_CONCAT(DISTINCT t.id) as tag_ids,
                GROUP_CONCAT(DISTINCT t.slug) as tag_slugs,
                (SELECT COUNT(*) FROM comment cm WHERE cm.post_id = p.id AND cm.is_enabled = 1) as number_of_comments
                FROM post p
                LEFT JOIN user u ON p.author_id = u.id
                LEFT JOIN category c ON p.category_id = c.id
                LEFT JOIN post_tag pt ON p.id = pt.post_id
                LEFT JOIN tag t ON pt.tag_id = t.id
                WHERE p.id = :id
                GROUP BY p.id';
            $statement = $this->database->prepare($sql);
            $statement->execute(['id' => $id]);
            $data = $statement->fetch(\PDO::FETCH_ASSOC);
            if (!$data) {
                return null;
            }

            return $this->preparePostData($data);
        } catch (\PDOException $error) {
            throw new \PDOException($error->getMessage(), (int) $error->getCode());
        }
    }

    public function findBy(string $field, $value): array
    {
        try {
            $sql = "SELECT p.id as post_id, p.title, p.author_id, p.content, p.chapo, p.created_at, p.updated_at, p.is_enabled, p.featured_image, p.category_id, p.slug,
                        u.id as user_id, u.*,
                        c.id as category_id, c.name as category_name, c.slug as category_slug,
                    GROUP_CONCAT(DISTINCT t.name) as tag_names,
                    GROUP_CONCAT(DISTINCT t.id) as tag_ids,
                    GROUP_CONCAT(DISTINCT t.slug) as tag_slugs,
                    (SELECT COUNT(*) FROM comment cm WHERE cm.post_id = p.id AND cm.is_enabled = 1) as number_of_comments
                    FROM post p
                    LEFT JOIN user u ON p.author_id = u.id
                    LEFT JOIN category c ON p.category_id = c.id
                    LEFT JOIN tag t ON instr(',' || p.tags || ',', ',' || t.id || ',') > 0
                    WHERE {$field} = :value
                    GROUP BY p.id";
            $statement = $this->database->prepare($sql);
            $statement->execute(['value' => $value]);
            $posts = [];
            while ($data = $statement->fetch(\PDO::FETCH_ASSOC)) {
                $posts[] = $this->preparePostData($data);
            }

            return $posts;
        } catch (\PDOException $error) {
            throw new \PDOException($error->getMessage(), (int) $error->getCode());
        }
    }

    public function findOneBy(string $field, $value): ?PostModel
    {
        try {
            $sql = "SELECT p.id as post_id, p.title, p.author_id, p.content, p.chapo, p.created_at, p.updated_at, p.is_enabled, p.featured_image, p.category_id, p.slug,
                        u.id as user_id, u.*,
                        c.id as category_id, c.name as category_name, c.slug as category_slug,
                    GROUP_CONCAT(DISTINCT t.name) as tag_names,
                    GROUP_CONCAT(DISTINCT t.id) as tag_ids,
                    GROUP_CONCAT(DISTINCT t.slug) as tag_slugs,
                    (SELECT COUNT(*) FROM comment cm WHERE cm.post_id = p.id AND cm.is_enabled = 1) as number_of_comments
                    FROM post p
                    LEFT JOIN user u ON p.author_id = u.id
                    LEFT JOIN category c ON p.category_id = c.id
                    LEFT JOIN tag t ON instr(',' || p.tags || ',', ',' || t.id || ',') > 0
                    WHERE p.{$field} = :value
                    GROUP BY p.id
                    LIMIT 1";
            $statement = $this->database->prepare($sql);
            $statement->execute(['value' => $value]);
            $data = $statement->fetch(\PDO::FETCH_ASSOC);
            if (!$data) {
                return null;
            }

            return $this->preparePostData($data);
        } catch (\PDOException $error) {
            throw new \PDOException($error->getMessage(), (int) $error->getCode());
        }
    }

    public function findAll(int $page, int $limit): array
    {
        try {
            $sql = 'SELECT p.id as post_id, p.title, p.author_id, p.content, p.chapo, p.created_at, p.updated_at, p.is_enabled, p.featured_image, p.category_id, p.slug,
                        u.id as user_id, u.*,
                        c.id as category_id, c.name as category_name, c.slug as category_slug,
                    GROUP_CONCAT(DISTINCT t.name) as tag_names,
                    GROUP_CONCAT(DISTINCT t.id) as tag_ids,
                    GROUP_CONCAT(DISTINCT t.slug) as tag_slugs,
                    (SELECT COUNT(*) FROM comment cm WHERE cm.post_id = p.id AND cm.is_enabled = 1) as number_of_comments
                    FROM post p
                    LEFT JOIN user u ON p.author_id = u.id
                    LEFT JOIN category c ON p.category_id = c.id
                    LEFT JOIN tag t ON instr("," || p.tags || ",", "," || t.id || ",") > 0
                    GROUP BY p.id
                    ORDER BY p.created_at DESC
                    LIMIT :limit OFFSET :offset';
            $offset = ($page - 1) * $limit;
            $statement = $this->database->prepare($sql);
            $statement->bindValue('limit', $limit, \PDO::PARAM_INT);
            $statement->bindValue('offset', $offset, \PDO::PARAM_INT);
            $statement->execute();
            $posts = [];
            while ($data = $statement->fetch(\PDO::FETCH_ASSOC)) {
                $posts[] = $this->preparePostData($data);
            }

            return [
                'posts' => $posts,
                'total_posts' => $this->countAll(),
            ];
        } catch (\PDOException $error) {
            throw new \PDOException($error->getMessage(), (int) $error->getCode());
        }
    }

    public function countAll(): int
    {
        try {
            $sql = 'SELECT COUNT(*) as total_posts FROM post';
            $statement = $this->database->prepare($sql);
            $statement->execute();
            $data = $statement->fetch(\PDO::FETCH_ASSOC);

            return (int) $data['total_posts'];
        } catch (\PDOException $error) {
            throw new \PDOException($error->getMessage(), (int) $error->getCode());
        }
    }

    public function findPostsBetweenDates(\DateTime $startDate, \DateTime $endDate): array
    {
        try {
            $sql = "SELECT p.id as post_id, p.title, p.author_id, p.content, p.chapo, p.created_at, p.updated_at, p.is_enabled, p.featured_image, p.category_id, p.slug,
                        u.id as user_id, u.*,
                        c.id as category_id, c.name as category_name, c.slug as category_slug,
                    GROUP_CONCAT(DISTINCT t.name) as tag_names,
                    GROUP_CONCAT(DISTINCT t.id) as tag_ids,
                    GROUP_CONCAT(DISTINCT t.slug) as tag_slugs,
                    (SELECT COUNT(*) FROM comment cm WHERE cm.post_id = p.id AND cm.is_enabled = 1) as number_of_comments
                    FROM post p
                    LEFT JOIN user u ON p.author_id = u.id
                    LEFT JOIN category c ON p.category_id = c.id
                    LEFT JOIN tag t ON instr(',' || p.tags || ',', ',' || t.id || ',') > 0
                    WHERE p.created_at BETWEEN :start_date AND :end_date AND p.is_enabled = 1
                    GROUP BY p.id
                    ORDER BY p.created_at DESC";
            $statement = $this->database->prepare($sql);
            $statement->execute([
                'start_date' => $startDate->format('Y-m-d H:i:s'),
                'end_date' => $endDate->format('Y-m-d H:i:s'),
            ]);
            $posts = [];
            while ($data = $statement->fetch(\PDO::FETCH_ASSOC)) {
                $posts[] = $this->preparePostData($data);
            }

            return $posts;
        } catch (\PDOException $error) {
            throw new \PDOException($error->getMessage(), (int) $error->getCode());
        }
    }

    public function findPostsWithTag(string $tag): array
    {
        try {
            $sql = "SELECT p.id as post_id, p.title, p.author_id, p.content, p.chapo, p.created_at, p.updated_at, p.is_enabled, p.featured_image, p.category_id, p.slug,
                        u.id as user_id, u.*,
                        c.id as category_id, c.name as category_name, c.slug as category_slug,
                    GROUP_CONCAT(DISTINCT t.name) as tag_names,
                    GROUP_CONCAT(DISTINCT t.id) as tag_ids,
                    GROUP_CONCAT(DISTINCT t.slug) as tag_slugs,
                (SELECT COUNT(*) FROM comment cm WHERE cm.post_id = p.id AND cm.is_enabled = 1) as number_of_comments
                FROM post p
                LEFT JOIN user u ON p.author_id = u.id
                LEFT JOIN category c ON p.category_id = c.id
                LEFT JOIN tag t ON instr(',' || p.tags || ',', ',' || t.id || ',') > 0
                WHERE instr(',' || p.tags || ',', ',' || (SELECT id FROM tag WHERE slug = :tag_slug) || ',') > 0 AND p.is_enabled = 1
                GROUP BY p.id
                ORDER BY p.created_at DESC";
            $statement = $this->database->prepare($sql);
            $statement->execute(['tag_slug' => $tag]);
            $posts = [];
            while ($data = $statement->fetch(\PDO::FETCH_ASSOC)) {
                $posts[] = $this->preparePostData($data);
            }

            return $posts;
        } catch (\PDOException $error) {
            throw new \PDOException($error->getMessage(), (int) $error->getCode());
        }
    }

    public function findUserPosts(int $userId, int $page, int $limit): array
    {
        try {
            $sql = "SELECT p.id as post_id, p.title, p.author_id, p.content, p.chapo, p.created_at, p.updated_at, p.is_enabled, p.featured_image, p.category_id, p.slug,
                        u.id as user_id, u.*,
                        c.id as category_id, c.name as category_name, c.slug as category_slug,
                    GROUP_CONCAT(DISTINCT t.name) as tag_names,
                    GROUP_CONCAT(DISTINCT t.id) as tag_ids,
                    GROUP_CONCAT(DISTINCT t.slug) as tag_slugs,
                (SELECT COUNT(*) FROM comment cm WHERE cm.post_id = p.id AND cm.is_enabled = 1) as number_of_comments
                FROM post p
                LEFT JOIN user u ON p.author_id = u.id
                LEFT JOIN category c ON p.category_id = c.id
                LEFT JOIN tag t ON instr(',' || p.tags || ',', ',' || t.id || ',') > 0
                WHERE p.author_id = :user_id
                GROUP BY p.id
                ORDER BY p.created_at DESC
                LIMIT :limit
                OFFSET :offset";
            $statement = $this->database->prepare($sql);
            $statement->execute([
                'user_id' => $userId,
                'limit' => $limit,
                'offset' => ($page - 1) * $limit,
            ]);
            $posts = [];
            while ($data = $statement->fetch(\PDO::FETCH_ASSOC)) {
                $posts[] = $this->preparePostData($data);
            }
            $sql = 'SELECT COUNT(*) FROM post WHERE author_id = :user_id';
            $statement = $this->database->prepare($sql);
            $statement->execute(['user_id' => $userId]);
            $count = $statement->fetchColumn();

            return [
                'posts' => $posts,
                'count' => $count,
            ];
        } catch (\PDOException $error) {
            throw new \PDOException($error->getMessage(), (int) $error->getCode());
        }
    }

    public function findRecentPosts(int $limit = 5): array
    {
        try {
            $sql = 'SELECT *
            FROM post
            WHERE is_enabled = 1
            GROUP BY id
            ORDER BY created_at DESC
            LIMIT :limit';
            $statement = $this->database->prepare($sql);
            $statement->execute(['limit' => $limit]);
            $posts = [];
            while ($data = $statement->fetch(\PDO::FETCH_ASSOC)) {
                $posts[] = $this->preparePostData($data);
            }

            return $posts;
        } catch (\PDOException $error) {
            throw new \PDOException($error->getMessage(), (int) $error->getCode());
        }
    }

    public function count(): int
    {
        try {
            $sql = 'SELECT COUNT(*) FROM post';
            $statement = $this->database->prepare($sql);
            $statement->execute();

            return (int) $statement->fetchColumn();
        } catch (\PDOException $error) {
            throw new \PDOException($error->getMessage(), (int) $error->getCode());
        }
    }

    public function create(array $postData): ?PostModel
    {
        try {
            $sql = 'INSERT INTO post (title, content, author_id, chapo, created_at, updated_at, is_enabled, featured_image, category_id, slug, tags) VALUES (:title, :content, :author_id, :chapo, :created_at, :updated_at, :is_enabled, :featured_image, :category_id, :slug, :tags)';
            $statement = $this->database->prepare($sql);
            $params = [
                'title' => $postData['title'],
                'content' => $postData['content'],
                'author_id' => $postData['author'],
                'chapo' => $postData['chapo'],
                'created_at' => $postData['createdAt'],
                'updated_at' => $postData['updatedAt'],
                'is_enabled' => $postData['isEnabled'],
                'featured_image' => $postData['featuredImage'],
                'category_id' => $postData['category'],
                'slug' => $postData['slug'],
                'tags' => $postData['tags'],
            ];
            $statement->execute($params);
            $lastInsertId = $this->database->lastInsertId();
            $post = $this->find((int) $lastInsertId);
            if (null !== $post) {
                return $post;
            }
        } catch (\PDOException $error) {
            throw new \PDOException($error->getMessage(), (int) $error->getCode());
        }
    }

    public function updatePost(PostModel $post, array $data): bool
    {
        $tagIds = implode(',', array_map(function ($tag) {
            return $tag->getId();
        }, $post->getTags()));
        $sql = 'UPDATE post SET title = :title, content = :content, chapo = :chapo, updated_at = :updated_at, is_enabled = :is_enabled, featured_image = :featured_image, category_id = :category_id, slug = :slug, tags = :tags WHERE id = :id';
        $statement = $this->database->prepare($sql);
        $statement->bindValue('id', $post->getId(), \PDO::PARAM_INT);
        $statement->bindValue(':title', $data['title']);
        $statement->bindValue(':content', $data['content']);
        $statement->bindValue(':chapo', $data['chapo']);
        $statement->bindValue(':featured_image', $data['featuredImage']);
        $statement->bindValue(':updated_at', $data['updatedAt']);
        $statement->bindValue(':is_enabled', (int) $data['isEnabled']);
        $statement->bindValue(':category_id', $data['category']);
        $statement->bindValue(':slug', $data['slug']);
        $statement->bindValue(':tags', $tagIds);
        $statement->execute();

        return true;
    }

    public function updatePostTags(PostModel $post, array $tags): bool
    {
        $deleteSql = 'DELETE FROM post_tag WHERE post_id = :post_id';
        $deleteStatement = $this->database->prepare($deleteSql);
        $deleteStatement->bindValue('post_id', $post->getId(), \PDO::PARAM_INT);
        $deleteStatement->execute();
        $insertSql = 'INSERT INTO post_tag (post_id, tag_id) VALUES (:post_id, :tag_id)';
        $insertStatement = $this->database->prepare($insertSql);
        foreach ($tags as $tag) {
            $insertStatement->bindValue('post_id', $post->getId(), \PDO::PARAM_INT);
            $insertStatement->bindValue('tag_id', $tag->getId(), \PDO::PARAM_INT);
            $insertResult = $insertStatement->execute();
            if (!$insertResult) {
                return false;
            }
        }

        return true;
    }

    public function updateIsEnabled(PostModel $post): bool
    {
        try {
            $sql = 'UPDATE post SET is_enabled = :isEnabled WHERE id = :id';
            $statement = $this->database->prepare($sql);
            $statement->bindValue(':isEnabled', $post->getIsEnabled(), \PDO::PARAM_BOOL);
            $statement->bindValue(':id', $post->getId(), \PDO::PARAM_INT);
            $statement->execute();

            return true;
        } catch (\PDOException $error) {
            throw new \PDOException($error->getMessage(), (int) $error->getCode());
        }
    }

    public function preparePostData(array $data): PostModel
    {
        $data['author'] = $this->modelFactory->createModelFromArray(UserModel::class, $data);
        $data['category'] = $this->modelFactory->createModelFromArray(CategoryModel::class, $data);
        $data['tags'] = $this->modelFactory->createModelFromArray(TagModel::class, $data);
        $data['comments'] = $this->modelFactory->createModelFromArray(CommentModel::class, $data);

        return $this->modelFactory->createModelFromArray(PostModel::class, $data);
    }
}
