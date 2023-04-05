<?php

declare(strict_types=1);

namespace App\Model;

class PostModel
{
    private $id;
    private $title;
    private $content;
    private $chapo;
    private $createdAt;
    private $updatedAt;
    private $isEnabled;
    private $featuredImage;
    private $author;
    private $category;
    private $slug;
    private string $categorySlug;
    private $tags;

    public function __construct(
        int $id,
        string $title,
        string $content,
        string $chapo,
        string $createdAt,
        string $updatedAt,
        bool $isEnabled,
        ?string $featuredImage,
        string $author,
        string $category,
        string $slug,
        string $categorySlug,
        array $tags
    ) {
        $this->id = $id;
        $this->title = $title;
        $this->content = $content;
        $this->chapo = $chapo;
        $this->createdAt = $createdAt;
        $this->updatedAt = $updatedAt;
        $this->isEnabled = $isEnabled;
        $this->featuredImage = $featuredImage;
        $this->author = $author;
        $this->category = $category;
        $this->slug = $slug;
        $this->categorySlug = $categorySlug;
        $this->tags = $tags;
    }

    public function __toString(): string
    {
        return json_encode($this->toArray());
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getContent(): string
    {
        return $this->content;
    }

    public function getChapo(): string
    {
        return $this->chapo;
    }

    public function getCreatedAt(): string
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): string
    {
        return $this->updatedAt;
    }

    public function getIsEnabled(): bool
    {
        return $this->isEnabled;
    }

    public function getFeaturedImage(): ?string
    {
        return $this->featuredImage;
    }

    public function getAuthor(): string
    {
        return $this->author;
    }

    public function getCategory(): string
    {
        return $this->category;
    }

    public function getCategorySlug(): string
    {
        return $this->categorySlug;
    }

    public function getSlug(): string
    {
        return $this->slug;
    }

    public function getTags(): array
    {
        return $this->tags;
    }

    public function setId(int $id): self
    {
        $this->id = $id;

        return $this;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function setContent(string $content): self
    {
        $this->content = $content;

        return $this;
    }

    public function setChapo(string $chapo): self
    {
        $this->chapo = $chapo;

        return $this;
    }

    public function setCreatedAt(string $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function setUpdatedAt(string $updatedAt): self
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    public function setIsEnabled(bool $isEnabled): self
    {
        $this->isEnabled = $isEnabled;

        return $this;
    }

    public function setFeaturedImage(?string $featuredImage): self
    {
        $this->featuredImage = $featuredImage;

        return $this;
    }

    public function setAuthor(array $author): self
    {
        $this->author = $author;

        return $this;
    }

    public function setCategory(array $category): self
    {
        $this->category = $category;

        return $this;
    }

    public function setSlug(string $slug): self
    {
        $this->slug = $slug;

        return $this;
    }

    public function setTags(array $tags): self
    {
        $this->tags = $tags;

        return $this;
    }

    public function setCategorySlug(string $categorySlug): self
    {
        $this->categorySlug = $categorySlug;

        return $this;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'content' => $this->content,
            'chapo' => $this->chapo,
            'createdAt' => $this->createdAt,
            'updatedAt' => $this->updatedAt,
            'isEnabled' => $this->isEnabled,
            'featuredImage' => $this->featuredImage,
            'author' => $this->author,
            'category' => $this->category,
            'category_slug' => $this->categorySlug,
            'slug' => $this->slug,
            'tags' => $this->tags,
        ];
    }

    public static function fromArray(array $data): self
    {
        return new self(
            $data['id'],
            $data['title'],
            $data['content'],
            $data['chapo'],
            $data['createdAt'],
            $data['updatedAt'],
            $data['isEnabled'],
            $data['featuredImage'],
            $data['author'],
            $data['category'],
            $data['slug'],
            $data['category_slug'],
            $data['tags']
        );
    }

    public function jsonSerialize(): array
    {
        return $this->toArray();
    }
}
