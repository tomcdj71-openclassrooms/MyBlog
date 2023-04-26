<?php

declare(strict_types=1);

namespace App\ModelParameters;

class TagModelParameters
{
    public $id;
    public $name;
    public $slug;

    public static function createFromData(array $data): self
    {
        $tagModelParams = new self();
        $tagModelParams->id = (int) ($data['tag_id'] ?? $data['id']);
        $tagModelParams->name = $data['name'];
        $tagModelParams->slug = $data['slug'];

        return $tagModelParams;
    }
}
