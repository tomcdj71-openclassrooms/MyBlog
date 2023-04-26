<?php

declare(strict_types=1);

namespace App\ModelParameters;

class CategoryModelParameters
{
    public $id;
    public $name;
    public $slug;

    public static function createFromData(array $data): self
    {
        $categoryModelParams = new self();
        $categoryModelParams->id = (int) ($data['category_id'] ?? $data['id']);
        $categoryModelParams->name = $data['name'];
        $categoryModelParams->slug = $data['slug'];

        return $categoryModelParams;
    }
}
