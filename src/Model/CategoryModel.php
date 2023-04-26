<?php

declare(strict_types=1);

namespace App\Model;

class CategoryModel
{
    private $id;
    private $name;
    private $slug;
    private int $nbPosts;

    public function __construct(int $id, string $name, string $slug, int $nbPosts = 0)
    {
        $this->id = $id;
        $this->name = $name;
        $this->slug = $slug;
        $this->nbPosts = $nbPosts;
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

    public function getNbPosts(): int
    {
        return $this->nbPosts;
    }

    public function setNbPosts(int $nbPosts): void
    {
        $this->nbPosts = $nbPosts;
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

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
        ];
    }
}
