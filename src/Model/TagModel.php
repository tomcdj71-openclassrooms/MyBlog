<?php

declare(strict_types=1);

namespace App\Model;

class TagModel
{
    private $id;
    private $name;
    private $slug;

    public function __construct(int $id, string $name, string $slug)
    {
        $this->id = $id;
        $this->name = $name;
        $this->slug = $slug;
    }

    public function __toString(): string
    {
        return json_encode($this->toArray());
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

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
        ];
    }

    public function fromArray(array $data): void
    {
        $this->id = $data['id'];
        $this->name = $data['name'];
        $this->slug = $data['slug'];
    }

    public function jsonSerialize(): array
    {
        return $this->toArray();
    }
}
