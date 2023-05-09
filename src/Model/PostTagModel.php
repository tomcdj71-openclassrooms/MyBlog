<?php

namespace App\Model;

class PostTagModel
{
    private int $postId;
    private int $tagId;

    public function __construct(int $postId, int $tagId)
    {
        $this->postId = $postId;
        $this->tagId = $tagId;
    }

    public function getPostId(): int
    {
        return $this->postId;
    }

    public function setPostId(int $postId): void
    {
        $this->postId = $postId;
    }

    public function getTagId(): int
    {
        return $this->tagId;
    }

    public function setTagId(int $tagId): void
    {
        $this->tagId = $tagId;
    }
}
