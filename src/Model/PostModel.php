<?php

declare(strict_types=1);

namespace App\Model;

use App\ModelParameters\PostModelParameters;

class PostModel
{
    private $id;
    private $title;
    private ?UserModel $author = null;
    private $content;
    private $chapo;
    private $createdAt;
    private $updatedAt;
    private $isEnabled;
    private $featuredImage;
    private ?CategoryModel $category = null;
    private $slug;
    private array $comments;
    private array $tags;

    public function __construct(PostModelParameters $postModelParams)
    {
        $this->id = $postModelParams->id;
        $this->title = $postModelParams->title;
        $this->author = $postModelParams->author;
        $this->content = $postModelParams->content;
        $this->chapo = $postModelParams->chapo;
        $this->createdAt = $postModelParams->createdAt;
        $this->updatedAt = $postModelParams->updatedAt;
        $this->isEnabled = $postModelParams->isEnabled;
        $this->featuredImage = $postModelParams->featuredImage;
        $this->category = $postModelParams->category;
        $this->slug = $postModelParams->slug;
        $this->comments = $postModelParams->comments;
        $this->tags = $postModelParams->tags;
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

    public function getAuthor(): ?UserModel
    {
        return $this->author;
    }

    public function getCategory(): ?CategoryModel
    {
        return $this->category;
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

    public function setCategory(CategoryModel $category): self
    {
        $this->category = $category;

        return $this;
    }

    public function setSlug(string $slug): self
    {
        $this->slug = $slug;

        return $this;
    }

    public function addTags(array $tags): void
    {
        $this->tags = $tags;
    }

    public function getComments(): array
    {
        return $this->comments;
    }
}
