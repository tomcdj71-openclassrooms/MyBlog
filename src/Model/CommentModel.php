<?php

declare(strict_types=1);

namespace App\Model;

class CommentModel
{
    private $id;
    private $createdAt;
    private $content;
    private ?UserModel $author = null;
    private $isEnabled;
    private $parentId;
    private ?PostModel $post = null;

    public function __construct(
        int $id,
        string $content,
        string $createdAt,
        UserModel $author,
        bool $isEnabled,
        int $parentId = null,
        PostModel $post
    ) {
        $this->id = $id;
        $this->content = $content;
        $this->createdAt = $createdAt;
        $this->author = $author ? $author : null;
        $this->isEnabled = $isEnabled;
        $this->parentId = $parentId ? $parentId : null;
        $this->post = $post ? $post : null;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getContent(): string
    {
        return $this->content;
    }

    public function getCreatedAt(): string
    {
        return $this->createdAt;
    }

    public function getAuthor(): ?UserModel
    {
        return $this->author;
    }

    public function getIsEnabled(): bool
    {
        return (bool) $this->isEnabled;
    }

    public function getParentId(): ?int
    {
        return $this->parentId;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function setContent(string $content): void
    {
        $this->content = $content;
    }

    public function setCreatedAt(string $createdAt): void
    {
        $this->createdAt = $createdAt;
    }

    public function setAuthor(string $author): void
    {
        $this->author = $author;
    }

    public function setIsEnabled(bool $isEnabled): void
    {
        $this->isEnabled = $isEnabled;
    }

    public function setParentId(int $parentId): void
    {
        $this->parentId = $parentId;
    }

    public function getPost(): ?PostModel
    {
        return $this->post;
    }

    public function setPost(?PostModel $post): self
    {
        $this->post = $post;

        return $this;
    }
}
