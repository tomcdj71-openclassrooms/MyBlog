<?php

declare(strict_types=1);

namespace App\Service;

use App\DependencyInjection\Container;
use App\Manager\PostManager;

class PostService extends AbstractService
{
    private PostManager $postManager;

    public function __construct(Container $container)
    {
        $container->injectProperties($this);
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
}
