<?php

declare(strict_types=1);

namespace App\Service;

use App\Helper\SecurityHelper;
use App\Manager\PostManager;
use App\Router\ServerRequest;

class PostService extends AbstractService
{
    public function __construct(ServerRequest $serverRequest, SecurityHelper $securityHelper, PostManager $postManager)
    {
        $this->serverRequest = $serverRequest;
        $this->securityHelper = $securityHelper;
        $this->postManager = $postManager;
    }

    public function getUserPostsData()
    {
        $user = $this->securityHelper->getUser();
        $offset = $this->serverRequest->getQuery('offset') ? intval($this->serverRequest->getQuery('offset')) : 1;
        $limit = $this->serverRequest->getQuery('limit') ? intval($this->serverRequest->getQuery('limit')) : 10;
        $page = intval($offset / $limit) + 1;
        $userPostsData = $this->postManager->findUserPosts($user->getId(), $page, $limit);
        $userPosts = $userPostsData['posts'];
        $userPostsArray = [];
        $comments = '';
        foreach ($userPosts as $post) {
            $numberOfComments = isset($comments['number_of_comments']) ? $comments['number_of_comments'] : 0;
            $tags = array_map(function ($tag) {
                return $tag->getName();
            }, $post->getTags());
            $userPostsArray[] = [
                'id' => $post->getId(),
                'title' => $post->getTitle(),
                'slug' => $post->getSlug(),
                'created_at' => $post->getCreatedAt(),
                'is_enabled' => $post->getIsEnabled(),
                'category' => $post->getCategory()->getName(),
                'comments' => $numberOfComments.' commentaire(s)',
                'tags' => $tags,
                'type' => 'myPosts',
            ];
        }
        $totalPosts = $userPostsData['count'];

        return [
            'rows' => $userPostsArray,
            'total' => $totalPosts,
        ];
    }

    public function getOtherUserPostsData(int $userId)
    {
        $offset = $this->serverRequest->getQuery('offset') ? intval($this->serverRequest->getQuery('offset')) : 1;
        $limit = $this->serverRequest->getQuery('limit') ? intval($this->serverRequest->getQuery('limit')) : 10;
        $page = intval($offset / $limit) + 1;
        $otherUserPostsData = $this->postManager->findUserPosts($userId, $page, $limit);
        $otherUserPosts = $otherUserPostsData['posts'];
        $otherUserPostsArray = [];
        $comments = '';
        foreach ($otherUserPosts as $post) {
            $numberOfComments = isset($comments['number_of_comments']) ? $comments['number_of_comments'] : 0;
            $tags = array_map(function ($tag) {
                return $tag->getName();
            }, $post->getTags());
            $otherUserPostsArray[] = [
                'id' => $post->getId(),
                'title' => $post->getTitle(),
                'slug' => $post->getSlug(),
                'created_at' => $post->getCreatedAt(),
                'is_enabled' => $post->getIsEnabled(),
                'category' => $post->getCategory()->getName(),
                'comments' => $numberOfComments.' commentaire(s)',
                'tags' => $tags,
                'type' => 'otherPosts',
            ];
        }
        $totalPosts = $otherUserPostsData['count'];

        return [
            'rows' => $otherUserPostsArray,
            'total' => $totalPosts,
        ];
    }
}
