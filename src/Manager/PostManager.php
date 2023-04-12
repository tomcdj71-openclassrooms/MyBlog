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

    public function findBy(array $criteria)
    {
        try {
            $sql = 'SELECT p.*, u.username as author_name, c.name as category_name, c.slug as category_slug, GROUP_CONCAT(t.name) as tag_names,
               (SELECT json_group_array(json_object(\'id\', cm.id, \'content\', cm.content, \'isEnabled\', cm.is_enabled, \'author_id\', cm.author_id, \'author_name\', cmu.username, \'created_at\', cm.created_at, \'parent_id\', cm.parent_id, \'avatar\', cmu.avatar))
                FROM comment cm
                LEFT JOIN user cmu ON cm.author_id = cmu.id
                WHERE cm.post_id = p.id) as comments,
                (SELECT COUNT(*) FROM comment cm WHERE cm.post_id = p.id AND cm.is_enabled = 1) as number_of_comments
                FROM post p
                LEFT JOIN user u ON p.author_id = u.id
                LEFT JOIN category c ON p.category_id = c.id
                LEFT JOIN tag t ON instr("," || p.tags || ",", "," || t.id || ",") > 0';

            $conditionsMap = [
                'category' => ['column' => 'c.slug', 'param' => 'category'],
                'tag' => ['column' => 't.slug', 'param' => 'tag'],
                'slug' => ['column' => 'p.slug', 'param' => 'slug'],
                'author' => ['column' => 'p.author_id', 'param' => 'author'],
            ];

            $conditions = [];
            $params = [];

            foreach ($criteria as $key => $value) {
                if (isset($conditionsMap[$key])) {
                    $conditions[] = "{$conditionsMap[$key]['column']} = :{$conditionsMap[$key]['param']}";
                    $params[$conditionsMap[$key]['param']] = $value;
                }
            }

            if (isset($criteria['from_date'], $criteria['to_date'])) {
                $conditions[] = 'p.created_at BETWEEN :from_date AND :to_date';
                $params['from_date'] = $criteria['from_date'];
                $params['to_date'] = $criteria['to_date'];
            }

            if (!empty($conditions)) {
                $sql .= ' WHERE '.implode(' AND ', $conditions);
            }

            $order = (isset($criteria['order']) && in_array(strtoupper($criteria['order']), ['ASC', 'DESC']))
                    ? strtoupper($criteria['order'])
                    : 'DESC';

            $sql .= ' GROUP BY p.id';
            $sql .= " ORDER BY p.created_at {$order}";

            if (isset($criteria['limit'])) {
                $sql .= ' LIMIT '.$criteria['limit'];
            }

            $statement = $this->db->prepare($sql);
            $statement->execute($params);

            $result = [];

            while ($data = $statement->fetch(\PDO::FETCH_ASSOC)) {
                $post = $this->createPostModelFromData($data);
                $result[] = $post;
            }

            return $result;
        } catch (\PDOException $e) {
            return 'Error: '.$e->getMessage();
        }
    }

    public function findAll(): array
    {
        try {
            $sql = 'SELECT p.*, u.username as author_name, c.name as category_name, c.slug as category_slug, GROUP_CONCAT(t.name) as tag_names,
                       (SELECT json_group_array(json_object(\'id\', cm.id, \'content\', cm.content, \'author_id\', cm.author_id, \'author_name\', cmu.username, \'created_at\', cm.created_at, \'parent_id\', cm.parent_id))
                        FROM comment cm
                        LEFT JOIN user cmu ON cm.author_id = cmu.id
                        WHERE cm.post_id = p.id AND cm.is_enabled = 1) as comments,
                        (SELECT COUNT(*) FROM comment cm WHERE cm.post_id = p.id AND cm.is_enabled = 1) as number_of_comments
                FROM post p
                LEFT JOIN user u ON p.author_id = u.id
                LEFT JOIN category c ON p.category_id = c.id
                LEFT JOIN tag t ON instr("," || p.tags || ",", "," || t.id || ",") > 0
                GROUP BY p.id
                ORDER BY p.created_at DESC';

            $statement = $this->db->prepare($sql);
            $statement->execute();

            $posts = [];
            $nestedComments = [];
            while ($data = $statement->fetch(\PDO::FETCH_ASSOC)) {
                $tags = array_map(function ($tag) {
                    return ['name' => $tag,
                        'slug' => strtolower(str_replace(' ', '-', $tag))];
                }, explode(',', $data['tag_names']));

                $comments = json_decode($data['comments'], true);
                $nestedComments = $this->buildNestedComments($comments);
                $commentsCount = $data['number_of_comments'];

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
                    $tags,
                    $nestedComments,
                    $commentsCount,
                );
            }

            return $posts;
        } catch (\PDOException $e) {
            return 'Error: '.$e->getMessage();
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

    private function createPostModelFromData(array $data): PostModel
    {
        $tags = array_map(function ($tag) {
            return ['name' => $tag,
                'slug' => strtolower(str_replace(' ', '-', $tag))];
        }, explode(',', $data['tag_names']));

        $comments = json_decode($data['comments'], true);
        $nestedComments = $this->buildNestedComments($comments);
        $commentsCount = $data['number_of_comments'];

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
            $tags,
            $nestedComments,
            $commentsCount,
        );
    }

    private function buildNestedComments($comments, $parentId = null)
    {
        $nestedComments = [];
        $byId = [];

        foreach ($comments as $comment) {
            $byId[$comment['id']] = $comment;
            $byId[$comment['id']]['children'] = [];
        }

        foreach ($byId as $id => $comment) {
            if (null === $comment['parent_id']) {
                $nestedComments[] = &$byId[$id];
            } else {
                if (isset($byId[$comment['parent_id']])) {
                    $byId[$comment['parent_id']]['children'][] = &$byId[$id];
                }
            }
        }

        return $nestedComments;
    }
}
