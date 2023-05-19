<?php

declare(strict_types=1);

namespace App\ModelParameters;

use App\Model\CategoryModel;
use App\Model\UserModel;

class PostModelParameters
{
    public $id;
    public $title;
    public ?UserModel $author = null;
    public $content;
    public $chapo;
    public $createdAt;
    public $updatedAt;
    public $isEnabled;
    public $featuredImage;
    public ?CategoryModel $category = null;
    public $slug;
    public array $comments = [];
    public array $tags = [];
    public $commentCount;

    public static function createFromData(array $data): self
    {
        $data['tags_array'] = $data['tags_array'] ?? $data['tags'];
        $postModelParams = new self();
        $postModelParams->id = (int) ($data['post_id'] ?? $data['id']);
        $postModelParams->title = $data['title'];
        $postModelParams->content = $data['content'];
        $postModelParams->chapo = $data['chapo'];
        $postModelParams->createdAt = $data['created_at'];
        $postModelParams->updatedAt = $data['updated_at'];
        $postModelParams->isEnabled = (bool) $data['is_enabled'];
        $postModelParams->featuredImage = $data['featured_image'];
        $postModelParams->author = $data['author'];
        $postModelParams->category = $data['category'];
        $postModelParams->slug = $data['slug'];
        $postModelParams->comments = $data['comments'];
        $postModelParams->tags = $data['tags'];
        $postModelParams->commentCount = $data['number_of_comments'] ?? $data['comment_count'] ?? 0;

        return $postModelParams;
    }
}
