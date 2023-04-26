<?php

declare(strict_types=1);

namespace App\ModelParameters;

class CommentModelParameters
{
    public $id;
    public $content;
    public $createdAt;
    public $updatedAt;
    public $isEnabled;
    public $author;
    public $post;
    public $parent;

    public static function createFromData(array $data): self
    {
        $commentModelParams = new self();
        $commentModelParams->id = (int) ($data['comment_id'] ?? $data['id']);
        $commentModelParams->content = $data['content'];
        $commentModelParams->createdAt = $data['created_at'];
        $commentModelParams->updatedAt = $data['updated_at'];
        $commentModelParams->isEnabled = (bool) $data['is_enabled'];
        $commentModelParams->author = $data['author'];
        $commentModelParams->post = $data['post'];
        $commentModelParams->parent = $data['parent'];

        return $commentModelParams;
    }
}
