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
use Tracy\Debugger;

class PostManager
{
    private \PDO $database;
    private PostModelParameters $postModelParams;
    private UserModelParameters $userModelParams;
    private TagModelParameters $tagModelParams;

    public function __construct(DatabaseConnexion $databaseConnexion)
    {
        $this->database = $databaseConnexion->connect();
        $this->postModelParams = new PostModelParameters();
        $this->userModelParams = new UserModelParameters();
        $this->tagModelParams = new TagModelParameters();
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
            $preparedData = $this->preparePostData($data);

            return $this->createPostModelFromArray($preparedData);
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
                $preparedData = $this->preparePostData($data);
                $posts[] = $this->createPostModelFromArray($preparedData);
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
            $preparedData = $this->preparePostData($data);

            return $this->createPostModelFromArray($preparedData);
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
                $preparedData = $this->preparePostData($data);
                $posts[] = $this->createPostModelFromArray($preparedData);
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
                WHERE instr(',' || p.tags || ',', ',' || (SELECT id FROM tag WHERE slug = :tag_slug) || ',') > 0
                GROUP BY p.id
                ORDER BY p.created_at DESC";
            $statement = $this->database->prepare($sql);
            $statement->execute(['tag_slug' => $tag]);
            $posts = [];
            while ($data = $statement->fetch(\PDO::FETCH_ASSOC)) {
                $preparedData = $this->preparePostData($data);
                $posts[] = $this->createPostModelFromArray($preparedData);
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
        } catch (\PDOException $error) {
            throw new \PDOException($error->getMessage(), (int) $error->getCode());
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
        $sql = 'UPDATE post SET title = :title, content = :content, chapo = :chapo, updated_at = :updated_at, is_enabled = :is_enabled, featured_image = :featured_image, category_id = :category_id, slug = :slug';
        $params = [
            'title' => $data['title'],
            'content' => $data['content'],
            'chapo' => $data['chapo'],
            'updated_at' => $data['updatedAt'],
            'is_enabled' => $data['isEnabled'],
            'featured_image' => $data['featuredImage'],
            'category_id' => $data['category'],
            'slug' => $data['slug'],
        ];
        $sql .= ' WHERE id = :id';
        $statement = $this->database->prepare($sql);
        $statement->bindValue('id', $post->getId(), \PDO::PARAM_INT);

        return $statement->execute($params);
    }

    public function updatePostTags(PostModel $post, array $tags): bool
    {
        $deleteSql = 'DELETE FROM post_tag WHERE post_id = :post_id';
        $deleteStatement = $this->database->prepare($deleteSql);
        $deleteStatement->bindValue('post_id', $post->getId(), \PDO::PARAM_INT);
        $deleteResult = $deleteStatement->execute();

        Debugger::barDump(['deleteResult' => $deleteResult]);

        $insertSql = 'INSERT INTO post_tag (post_id, tag_id) VALUES (:post_id, :tag_id)';
        $insertStatement = $this->database->prepare($insertSql);

        foreach ($tags as $tag) {
            $insertStatement->bindValue('post_id', $post->getId(), \PDO::PARAM_INT);
            $insertStatement->bindValue('tag_id', $tag->getId(), \PDO::PARAM_INT);
            $insertResult = $insertStatement->execute();

            Debugger::barDump(['insertResult' => $insertResult, 'tag' => $tag]);

            if (!$insertResult) {
                return false;
            }
        }

        return true;
    }

    public function updateIsEnabled(PostModel $post): bool
    {
        try {
            $sql = 'UPDATE comment SET is_enabled = :isEnabled WHERE id = :id';
            $statement = $this->database->prepare($sql);
            $statement->bindValue(':isEnabled', $post->getIsEnabled(), \PDO::PARAM_BOOL);
            $statement->bindValue(':id', $post->getId(), \PDO::PARAM_INT);
            $statement->execute();

            return true;
        } catch (\PDOException $error) {
            throw new \PDOException($error->getMessage(), (int) $error->getCode());
        }
    }

    protected function createPostModelFromArray(array $data): PostModel
    {
        if (!isset($data['tags'])) {
            $data['tags'] = [];
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

    private function prepareAuthor(array $data): ?UserModel
    {
        $authorModelParams = $this->userModelParams->createFromData($data);

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
            $tagModelParams = $this->tagModelParams->createFromData($tagsData);
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
        $data['tags'] = $this->prepareTags($data);
        $data['comments'] = $this->prepareComments($data);

        return $data;
    }
}
