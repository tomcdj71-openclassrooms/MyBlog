<?php

declare(strict_types=1);

namespace App\Model;

class PostModel
{
    public $author;
    private $id;
    private $title;
    private $content;
    private $chapo;
    private $createdAt;
    private $updatedAt;
    private $isEnabled;
    private $featuredImage;
    private $category;
    private $slug;
    private string $categorySlug;
    private $tags;
    private $comments;
    private $numberOfComments;

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
        array $tags,
        array $comments = [],
        ?int $numberOfComments = null
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
        $this->comments = $comments;
        $this->numberOfComments = $numberOfComments;
    }

    public function __toString(): string
    {
        return json_encode($this->__toArray());
    }

    public function __toArray(): array
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
            'slug' => $this->slug,
            'categorySlug' => $this->categorySlug,
            'tags' => $this->tags,
            'comments' => $this->comments,
        ];
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

    public function getComments(): array
    {
        return $this->comments;
    }

    public function setComments(array $comments): self
    {
        $this->comments = $comments;

        return $this;
    }

    public function addComment(array $comment): self
    {
        $this->comments[] = $comment;

        return $this;
    }

    public function removeComment(array $comment): self
    {
        $this->comments = array_filter($this->comments, function ($c) use ($comment) {
            return $c['id'] !== $comment['id'];
        });

        return $this;
    }

    public function getNumberOfComments(): int
    {
        return count($this->comments);
    }

    public function getNumberOfEnabledComments(): int
    {
        return count(array_filter($this->comments, function ($comment) {
            return $comment['isEnabled'];
        }));
    }

    public function setNumberOfComments(int $numberOfComments): self
    {
        $this->numberOfComments = $numberOfComments;

        return $this;
    }
}
