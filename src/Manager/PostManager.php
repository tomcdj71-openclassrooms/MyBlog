<?php

declare(strict_types=1);

namespace App\Manager;

use App\Config\DatabaseConnexion;
use App\Model\CategoryModel;
use App\Model\CommentModel;
use App\Model\PostModel;
use App\Model\TagModel;
use App\Model\UserModel;
use App\ModelParameters\PostModelParameters;
use App\ModelParameters\TagModelParameters;
use App\ModelParameters\UserModelParameters;

class PostManager
{
    private \PDO $database;
    private PostModelParameters $postModelParams;

    public function __construct(DatabaseConnexion $databaseConnexion)
    {
        $this->database = $databaseConnexion->connect();
        $this->postModelParams = new PostModelParameters();
    }

    public function find(int $postId): ?PostModel
    {
        try {
            $sql = 'SELECT p.*, u.*, c.*,
                    GROUP_CONCAT(DISTINCT t.name) as tag_names,
                    GROUP_CONCAT(DISTINCT t.id) as tag_ids,
                    GROUP_CONCAT(DISTINCT t.slug) as tag_slugs,
                    (SELECT COUNT(*) FROM comment cm WHERE cm.post_id = p.id AND cm.is_enabled = 1) as number_of_comments
                    FROM post p
                    LEFT JOIN user u ON p.author_id = u.id
                    LEFT JOIN category c ON p.category_id = c.id
                    LEFT JOIN tag t ON instr("," || p.tags || ",", "," || t.id || ",") > 0
                    WHERE p.id = :id
                    GROUP BY p.id';
            $statement = $this->database->prepare($sql);
            $statement->execute(['id' => $postId]);
            $data = $statement->fetch(\PDO::FETCH_ASSOC);
            if (!$data) {
                return null;
            }

            return $this->createPostModelFromArray($data);
        } catch (\PDOException $e) {
            error_log('Error fetching post: '.$e->getMessage());

            return null;
        }
    }

    public function findBy(string $field, $value): array
    {
        try {
            $sql = "SELECT p.*, u.*, c.name as category_name, c.slug as category_slug,
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
                $preparedData = $this->preparePostData($data);
                $posts[] = $this->createPostModelFromArray($preparedData);
            }

            return $posts;
        } catch (\PDOException $e) {
            error_log('Error fetching posts: '.$e->getMessage());

            return [];
        }
    }

    public function findOneBy(string $field, $value): ?PostModel
    {
        try {
            $sql = "SELECT p.id as post_id, p.*, u.id as user_id, u.*, c.id as category_id, c.name as category_name, c.slug as category_slug,
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
            $preparedData = $this->preparePostData($data);

            return $this->createPostModelFromArray($preparedData);
        } catch (\PDOException $e) {
            error_log('Error fetching post: '.$e->getMessage());

            return null;
        }
    }

    public function findAll(int $page, int $limit): array
    {
        try {
            $sql = 'SELECT p.*, u.*, c.name as category_name, c.slug as category_slug,
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
            $statement = $this->database->prepare($sql);
            $statement->bindValue('limit', $limit, \PDO::PARAM_INT);
            $statement->bindValue('offset', ($page - 1) * $limit, \PDO::PARAM_INT);
            $statement->execute();
            $posts = [];
            while ($data = $statement->fetch(\PDO::FETCH_ASSOC)) {
                $preparedData = $this->preparePostData($data);
                $posts[] = $this->createPostModelFromArray($preparedData);
            }

            return [
                'posts' => $posts,
                'total_posts' => $this->countAll(),
            ];
        } catch (\PDOException $e) {
            error_log('Error fetching posts: '.$e->getMessage());

            return [];
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
        } catch (\PDOException $e) {
            error_log('Error counting posts: '.$e->getMessage());

            return 0;
        }
    }

    public function findPostsBetweenDates(\DateTime $startDate, \DateTime $endDate): array
    {
        try {
            $sql = "SELECT p.*, u.*, c.name as category_name, c.slug as category_slug,
                        GROUP_CONCAT(DISTINCT t.name) as tag_names,
                        GROUP_CONCAT(DISTINCT t.id) as tag_ids,
                        GROUP_CONCAT(DISTINCT t.slug) as tag_slugs,
                    (SELECT COUNT(*) FROM comment cm WHERE cm.post_id = p.id AND cm.is_enabled = 1) as number_of_comments
                    FROM post p
                    LEFT JOIN user u ON p.author_id = u.id
                    LEFT JOIN category c ON p.category_id = c.id
                    LEFT JOIN tag t ON instr(',' || p.tags || ',', ',' || t.id || ',') > 0
                    WHERE p.created_at BETWEEN :start_date AND :end_date
                    GROUP BY p.id
                    ORDER BY p.created_at DESC";
            $statement = $this->database->prepare($sql);
            $statement->execute([
                'start_date' => $startDate->format('Y-m-d H:i:s'),
                'end_date' => $endDate->format('Y-m-d H:i:s'),
            ]);
            $posts = [];
            while ($data = $statement->fetch(\PDO::FETCH_ASSOC)) {
                $preparedData = $this->preparePostData($data);
                $posts[] = $this->createPostModelFromArray($preparedData);
            }

            return $posts;
        } catch (\PDOException $e) {
            error_log('Error fetching posts: '.$e->getMessage());

            return [];
        }
    }

    public function findPostsWithTag(string $tag): array
    {
        try {
            $sql = "SELECT p.*, u.*, c.name as category_name, c.slug as category_slug,
                GROUP_CONCAT(DISTINCT t.name) as tag_names,
                GROUP_CONCAT(DISTINCT t.id) as tag_ids,
                GROUP_CONCAT(DISTINCT t.slug) as tag_slugs,
                (SELECT COUNT(*) FROM comment cm WHERE cm.post_id = p.id AND cm.is_enabled = 1) as number_of_comments
                FROM post p
                LEFT JOIN user u ON p.author_id = u.id
                LEFT JOIN category c ON p.category_id = c.id
                LEFT JOIN tag t ON instr(',' || p.tags || ',', ',' || t.id || ',') > 0
                WHERE instr(',' || p.tags || ',', ',' || (SELECT id FROM tag WHERE slug = :tag_slug) || ',') > 0
                GROUP BY p.id";
            $statement = $this->database->prepare($sql);
            $statement->execute(['tag_slug' => $tag]);
            $posts = [];
            while ($data = $statement->fetch(\PDO::FETCH_ASSOC)) {
                $preparedData = $this->preparePostData($data);
                $posts[] = $this->createPostModelFromArray($preparedData);
            }

            return $posts;
        } catch (\PDOException $e) {
            error_log('Error fetching posts: '.$e->getMessage());

            return [];
        }
    }

    public function findUserPosts(int $userId, int $page, int $limit): array
    {
        try {
            $sql = "SELECT p.*, u.*, c.name as category_name, c.slug as category_slug,
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
                $preparedData = $this->preparePostData($data);
                $posts[] = $this->createPostModelFromArray($preparedData);
            }
            $sql = 'SELECT COUNT(*) FROM post WHERE author_id = :user_id';
            $statement = $this->database->prepare($sql);
            $statement->execute(['user_id' => $userId]);
            $count = $statement->fetchColumn();

            return [
                'posts' => $posts,
                'count' => $count,
            ];
        } catch (\PDOException $e) {
            error_log('Error fetching posts: '.$e->getMessage());

            return [];
        }
    }

    public function findRecentPosts(int $limit = 5): array
    {
        try {
            $sql = 'SELECT *
            FROM post
            GROUP BY id
            ORDER BY created_at DESC
            LIMIT :limit';
            $statement = $this->database->prepare($sql);
            $statement->execute(['limit' => $limit]);
            $posts = [];
            while ($data = $statement->fetch(\PDO::FETCH_ASSOC)) {
                $data['author'] = [];
                $data['category'] = [];
                $data['tags'] = [];
                $posts[] = $this->createPostModelFromArray($data);
            }

            return $posts;
        } catch (\PDOException $e) {
            error_log('Error fetching posts: '.$e->getMessage());

            return [];
        }
    }

    public function count(): int
    {
        try {
            $sql = 'SELECT COUNT(*) FROM post';
            $statement = $this->database->prepare($sql);
            $statement->execute();

            return (int) $statement->fetchColumn();
        } catch (\PDOException $e) {
            error_log('Error counting posts: '.$e->getMessage());

            return 0;
        }
    }

    private function prepareAuthor(array $data): ?UserModel
    {
        $authorModelParams = UserModelParameters::createFromData($data);

        return new UserModel($authorModelParams);
    }

    private function prepareCategory(array $data): ?CategoryModel
    {
        return new CategoryModel(
            $data['category_id'],
            $data['category_name'],
            $data['category_slug'],
        );
    }

    private function prepareTags(array $data): array
    {
        $tagIds = explode(',', $data['tag_ids']);
        $tagNames = explode(',', $data['tag_names']);
        $tagSlugs = explode(',', $data['tag_slugs']);
        $tagsArray = [];
        for ($i = 0; $i < count($tagIds); ++$i) {
            $tagsData = [
                'id' => $tagIds[$i],
                'name' => $tagNames[$i],
                'slug' => $tagSlugs[$i],
            ];
            $tagModelParams = TagModelParameters::createFromData($tagsData);
            $tagsArray[] = new TagModel($tagModelParams);
        }

        return $tagsArray;
    }

    private function prepareComments(array $data): array
    {
        $comments = [];
        if (!empty($data['comments'])) {
            foreach ($data['comments'] as $comment) {
                $comments[] = new CommentModel(
                    $comment['id'],
                    $comment['content'],
                    $comment['created_at'],
                    $comment['updated_at'],
                    $comment['is_enabled'],
                    $comment['author'],
                    $comment['post_id'],
                );
            }
        }
        $comments['number_of_comments'] = $data['number_of_comments'];

        return $comments;
    }

    private function preparePostData(array $data): array
    {
        $data['author'] = $this->prepareAuthor($data);
        $data['category'] = $this->prepareCategory($data);
        $data['tags_array'] = $this->prepareTags($data);
        $data['comments'] = $this->prepareComments($data);

        return $data;
    }

    private function createPostModelFromArray(array $data): PostModel
    {
        if (!isset($data['tags_array'])) {
            $data['tags_array'] = [];
        }
        if (!isset($data['comments'])) {
            $data['comments'] = [];
        }
        if (empty($data['author'])) {
            $data['author'] = null;
        }
        if (empty($data['category'])) {
            $data['category'] = null;
        }
        $postModelParams = $this->postModelParams->createFromData($data);

        return new PostModel($postModelParams);
    }
}
