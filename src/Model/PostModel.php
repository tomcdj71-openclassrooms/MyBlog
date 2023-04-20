<?php

declare(strict_types=1);

namespace App\Model;

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
    private array $tags;
    private array $comments;

    public function __construct(
        int $id,
        string $title,
        string $content,
        string $chapo,
        string $createdAt,
        string $updatedAt,
        bool $isEnabled,
        ?string $featuredImage,
        UserModel $author = null,
        CategoryModel $category = null,
        string $slug,
        array $tags,
        array $comments
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
        $this->tags = $tags;
        $this->comments = $comments;
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
            'tags' => $this->tags,
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

    public function addTags(array $tags): self
    {
        $this->tags = $tags;

        return $this;
    }

    public function removeTags(array $tags): self
    {
        $this->tags = $tags;

        return $this;
    }

    public function getComments(): array
    {
        return $this->comments;
    }

    public function addComment(CommentModel $comment): self
    {
        if (!$this->comments->contains($comment)) {
            $this->comments->add($comment);
            $comment->setPost($this);
        }

        return $this;
    }

    public function removeComment(CommentModel $comment): self
    {
        if ($this->comments->removeElement($comment)) {
            if ($comment->getPost() === $this) {
                $comment->setPost(null);
            }
        }

        return $this;
    }
}
