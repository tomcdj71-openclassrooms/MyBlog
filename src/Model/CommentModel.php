<?php

declare(strict_types=1);

namespace App\Model;

class CommentModel
{
    public $author;
    public $createdAt;
    private $id;
    private $content;
    private $isEnabled;
    private $parentId;
    private $postId;

    public function __construct(int $id, string $content, string $createdAt, int $author, int $postId, bool $isEnabled, int $parentId)
    {
        $this->id = $id;
        $this->content = $content;
        $this->createdAt = $createdAt;
        $this->author = $author;
        $this->postId = $postId;
        $this->isEnabled = $isEnabled;
        $this->parentId = $parentId;
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

    public function getAuthor(): int
    {
        return $this->author;
    }

    public function getPostId(): int
    {
        return $this->postId;
    }

    public function getIsEnabled(): bool
    {
        return (bool) $this->isEnabled;
    }

    public function getParentId(): int
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

    public function setPostId(int $postId): void
    {
        $this->postId = $postId;
    }

    public function setIsEnabled(bool $isEnabled): void
    {
        $this->isEnabled = $isEnabled;
    }

    public function setParentId(int $parentId): void
    {
        $this->parentId = $parentId;
    }
}
