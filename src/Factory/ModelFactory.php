<?php

declare(strict_types=1);

namespace App\Factory;

use App\Model\CategoryModel;
use App\Model\CommentModel;
use App\Model\PostModel;
use App\Model\TagModel;
use App\Model\UserModel;
use App\ModelParameters\PostModelParameters;
use App\ModelParameters\TagModelParameters;
use App\ModelParameters\UserModelParameters;

class ModelFactory
{
    public function createModelFromArray(string $modelClass, array $data)
    {
        switch ($modelClass) {
            case PostModel::class:
                return $this->createPostModelFromArray($data);

            case CommentModel::class:
                return $this->createCommentModelFromArray($data);

            case TagModel::class:
                return $this->createTagModelFromArray($data);

            case UserModel::class:
                return $this->createUserModelFromArray($data);

            case CategoryModel::class:
                return $this->createCategoryModelFromArray($data);

            default:
                error_log("Classe de modèle inconnue {$modelClass}");
        }
    }

    private function createPostModelFromArray(array $data): PostModel
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
        $postModelParams = PostModelParameters::createFromData($data);

        return new PostModel($postModelParams);
    }

    private function createCommentModelFromArray(array $data): array
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

        return $comments;
    }

    private function createTagModelFromArray(array $data): array
    {
        $tagsArray = [];
        if (!empty($data['tag_ids'])) {
            $tagIds = explode(',', $data['tag_ids']);
            $tagNames = explode(',', $data['tag_names']);
            $tagSlugs = explode(',', $data['tag_slugs']);
            for ($i = 0; $i < count($tagIds); ++$i) {
                $tagsData = [
                    'id' => $tagIds[$i],
                    'name' => $tagNames[$i],
                    'slug' => $tagSlugs[$i],
                ];
                $tagModelParams = TagModelParameters::createFromData($tagsData);
                $tagsArray[] = new TagModel($tagModelParams);
            }
        }

        return $tagsArray;
    }

    private function createUserModelFromArray(array $data): UserModel
    {
        $userModelParams = UserModelParameters::createFromData($data);

        return new UserModel($userModelParams);
    }

    private function createCategoryModelFromArray(array $data): CategoryModel
    {
        if (!isset($data['category_id']) || !isset($data['category_name']) || !isset($data['category_slug'])) {
            return new CategoryModel(0, 'Unknown', 'unknown');
        }

        return new CategoryModel(
            $data['category_id'],
            $data['category_name'],
            $data['category_slug'],
        );
    }
}
