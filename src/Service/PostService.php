<?php

declare(strict_types=1);

namespace App\Service;

use App\DependencyInjection\Container;
use App\Helper\SecurityHelper;
use App\Manager\PostManager;
use App\Middleware\AuthenticationMiddleware;

class PostService
{
    private PostManager $postManager;
    private SecurityHelper $securityHelper;
    private AuthenticationMiddleware $authMiddleware;

    public function __construct(Container $container)
    {
        $container->injectProperties($this);
    }

    public function getUserPostsData()
    {
        $user = $this->securityHelper->getUser();
        $offset = isset($_GET['offset']) ? intval($_GET['offset']) : 1;
        $limit = isset($_GET['limit']) ? intval($_GET['limit']) : 10;
        $page = intval($offset / $limit) + 1;
        $userPostsData = $this->postManager->findUserPosts($user->getId(), $page, $limit);
        $userPosts = $userPostsData['posts'];
        $userPostsArray = [];
        foreach ($userPosts as $post) {
            $numberOfComments = isset($comments['number_of_comments']) ? $comments['number_of_comments'] : 0;
            $tags = $post->getTags();
            $tagNames = array_map(function ($tag) {
                return $tag['name'];
            }, $tags);
            $userPostsArray[] = [
                'title' => $post->getTitle(),
                'slug' => $post->getSlug(),
                'created_at' => $post->getCreatedAt(),
                'is_enabled' => $post->getIsEnabled(),
                'category' => $post->getCategory()->getName(),
                'comments' => $numberOfComments.' commentaire(s)',
                'tags' => implode(', ', $tagNames),
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
