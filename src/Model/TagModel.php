<?php

declare(strict_types=1);

namespace App\Model;

use App\ModelParameters\TagModelParameters;

class TagModel
{
    private $id;
    private $name;
    private $slug;

    public function __construct(TagModelParameters $tagModelParams)
    {
        $this->id = $tagModelParams->id;
        $this->name = $tagModelParams->name;
        $this->slug = $tagModelParams->slug;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getSlug(): string
    {
        return $this->slug;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function setSlug(string $slug): void
    {
        $this->slug = $slug;
    }
}
