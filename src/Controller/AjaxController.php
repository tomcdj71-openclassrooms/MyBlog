<?php

namespace App\Controller;

use App\Config\Configuration;
use App\Helper\SecurityHelper;
use App\Helper\TwigHelper;
use App\Manager\CategoryManager;
use App\Manager\CommentManager;
use App\Manager\PostManager;
use App\Manager\TagManager;
use App\Manager\UserManager;
use App\Router\Request;
use App\Router\ServerRequest;
use App\Router\Session;
use App\Service\MailerService;
use App\Service\PostService;

class AjaxController extends AbstractController
{
    private CommentManager $commentManager;
    private PostService $postService;
    private TagManager $tagManager;
    private CategoryManager $categoryManager;
    private PostManager $postManager;
    private Configuration $configuration;
    private MailerService $mailerService;

    public function __construct(
        TwigHelper $twig,
        Session $session,
        ServerRequest $serverRequest,
        SecurityHelper $securityHelper,
        UserManager $userManager,
        Request $request,
        Configuration $configuration,
        MailerService $mailerService
    ) {
        parent::__construct($twig, $session, $serverRequest, $securityHelper, $userManager, $request);
        $this->configuration = $configuration;
        $this->mailerService = $mailerService;
    }

    public function myComments(bool $impersonate = false)
    {
        if ($impersonate) {
            $username = $this->serverRequest->getPath();
            $user = $this->userManager->findOneBy(['username' => $username]);
        } else {
            $user = $this->securityHelper->getUser();
        }
        if (!$user) {
            throw new \Exception('Utilisateur non trouvé');
        }
        $offset = $this->serverRequest->getQuery('offset', 1);
        $limit = $this->serverRequest->getQuery('limit', 10);
        $page = intval($offset / $limit) + 1;
        $userComments = $this->commentManager->findUserComments($user->getId(), $page, $limit);
        $totalComments = $this->commentManager->countUserComments($user->getId());
        $userCommentsArray = [];
        foreach ($userComments as $comment) {
            $userCommentsArray[] = [
                'id' => $comment->getId(),
                'content' => $comment->getContent(),
                'created_at' => $comment->getCreatedAt(),
                'parent_id' => $comment->getParentId(),
                'post' => [
                    'title' => $comment->getPost()->getTitle(),
                    'slug' => $comment->getPost()->getSlug(),
                ],
                'type' => 'myComments',
                'actions' => [
                    'voir' => '/post/'.$comment->getPost()->getSlug().'#comment-'.$comment->getId(),
                ],
            ];
        }
        $response = [
            'rows' => $userCommentsArray,
            'total' => $totalComments,
        ];

        header('Content-Type: application/json');
        echo json_encode($response);
    }

    public function myPosts()
    {
        $userId = $_GET['userId'] ?? null;
        if ($userId) {
            $userPostsData = $this->postService->getOtherUserPostsData($userId);
        } else {
            $userPostsData = $this->postService->getUserPostsData();
        }
        foreach ($userPostsData['rows'] as $key => $row) {
            $userPostsData['rows'][$key]['actions'] = [
                'voir' => '/blog/post/'.$userPostsData['rows'][$key]['slug'],
                'modifier' => '/admin/post/'.$userPostsData['rows'][$key]['id'].'/edit',
            ];
            $userPostsData['rows'][$key]['type'] = 'myPosts';
        }
        header('Content-Type: application/json');
        echo json_encode($userPostsData);
    }

    public function manageAllComments()
    {
        $this->securityHelper->denyAccessUnlessAdmin();

        $offset = $this->serverRequest->getQuery('offset', 1);
        $limit = $this->serverRequest->getQuery('limit', 10);
        $page = intval($offset / $limit) + 1;
        $results = $this->commentManager->findAll($page, $limit);
        $comments = $results['comments'];
        $totalComments = $results['total_comments'];
        $commentsArray = [];
        foreach ($comments as $comment) {
            $commentsArray[] = [
                'id' => $comment->getId(),
                'content' => $comment->getContent(),
                'created_at' => $comment->getCreatedAt(),
                'parent_id' => $comment->getParentId(),
                'is_enabled' => $comment->getIsEnabled(),
                'post' => [
                    'id' => $comment->getPost()->getId(),
                    'title' => $comment->getPost()->getTitle(),
                    'slug' => $comment->getPost()->getSlug(),
                ],
                'user' => [
                    'username' => $comment->getAuthor()->getUsername(),
                ],
                'type' => 'allComments',
                'actions' => [
                    'voir' => '/blog/post/'.$comment->getPost()->getSlug().'#comment-'.$comment->getId(),
                    'approuver' => '/ajax/admin-toggle-comment/'.$comment->getId(),
                    'refuser' => '/ajax/admin-toggle-comment/'.$comment->getId(),
                ],
            ];
        }

        $response = [
            'rows' => $commentsArray,
            'total' => $totalComments,
        ];

        header('Content-Type: application/json');
        echo json_encode($response);
    }

    public function allTags()
    {
        $this->securityHelper->denyAccessUnlessAdmin();

        $tags = $this->tagManager->findAll();
        $tagsArray = [];
        foreach ($tags as $tag) {
            $tagsArray[] = [
                'id' => $tag->getId(),
                'name' => $tag->getName(),
                'slug' => $tag->getSlug(),
                'type' => 'allTags',
                'actions' => [
                    'voir' => '/blog/tag/'.$tag->getSlug(),
                    'editer' => '/admin/tag/'.$tag->getId().'/edit',
                ],
            ];
        }
        $response = [
            'rows' => $tagsArray,
            'total' => count($tagsArray),
        ];

        header('Content-Type: application/json');
        echo json_encode($response);
    }

    public function allCategories()
    {
        $this->securityHelper->denyAccessUnlessAdmin();

        $categories = $this->categoryManager->findAll();
        $categoriesArray = [];
        foreach ($categories as $category) {
            $categoriesArray[] = [
                'id' => $category->getId(),
                'name' => $category->getName(),
                'slug' => $category->getSlug(),
                'type' => 'allCategories',
                'actions' => [
                    'voir' => '/blog/category/'.$category->getSlug(),
                    'editer' => '/admin/category/'.$category->getId().'/edit',
                ],
            ];
        }
        $response = [
            'rows' => $categoriesArray,
            'total' => count($categoriesArray),
        ];

        header('Content-Type: application/json');
        echo json_encode($response);
    }

    public function allUsers()
    {
        $this->securityHelper->denyAccessUnlessAdmin();

        $users = $this->userManager->findAll();
        $usersArray = [];
        foreach ($users as $user) {
            $usersArray[] = [
                'id' => $user->getId(),
                'username' => $user->getUsername(),
                'email' => $user->getEmail(),
                'roles' => $user->getRole(),
                'created_at' => $user->getCreatedAt(),
                'type' => 'allUsers',
                'actions' => [
                    'voir' => '/admin/user/'.$user->getId().'/edit',
                    'promote' => '/ajax/admin-promote-user/'.$user->getId(),
                    'demote' => '/ajax/admin-promote-user/'.$user->getId(),
                ],
            ];
        }
        $response = [
            'rows' => $usersArray,
            'total' => count($usersArray),
        ];
        header('Content-Type: application/json');
        echo json_encode($response);
    }

    public function allPosts()
    {
        $this->securityHelper->denyAccessUnlessAdmin();

        $offset = $this->serverRequest->getQuery('offset', 1);
        $limit = $this->serverRequest->getQuery('limit', 10);
        $page = intval($offset / $limit) + 1;
        $results = $this->postManager->findAll($page, $limit);
        $posts = $results['posts'];
        $totalPosts = $results['total_posts'];
        $postsArray = [];
        $tagsArray = [];
        foreach ($posts as $post) {
            foreach ($post->getTags() as $tag) {
                $tagsArray[] = $tag->getName();
            }
            $postsArray[] = [
                'id' => $post->getId(),
                'title' => $post->getTitle(),
                'slug' => $post->getSlug(),
                'is_enabled' => $post->getIsEnabled(),
                'created_at' => $post->getCreatedAt(),
                'updated_at' => $post->getUpdatedAt(),
                'tags' => $tagsArray,
                'category' => $post->getCategory()->getName(),
                'comments' => $post->getComments(),
                'author' => $post->getAuthor()->getUsername(),
                'type' => 'allPosts',
                'actions' => [
                    'voir' => '/blog/post/'.$post->getSlug(),
                    'editer' => '/admin/post/'.$post->getId().'/edit',
                    'publish' => '/ajax/admin-toggle-post/'.$post->getId(),
                    'unpublish' => '/ajax/admin-toggle-post/'.$post->getId(),
                ],
            ];
        }
        $response = [
            'rows' => $postsArray,
            'total' => $totalPosts,
        ];
        header('Content-Type: application/json');
        echo json_encode($response);
    }

    public function toggleCommentStatus(int $commentId)
    {
        $this->securityHelper->denyAccessUnlessAdmin();

        $comment = $this->commentManager->find($commentId);
        if (null === $comment) {
            $this->sendJsonResponse(['error' => 'Commentaire non trouvé.'], 404);

            return;
        }
        $comment->setIsEnabled(!$comment->getIsEnabled());
        $success = $this->commentManager->updateIsEnabled($comment);
        $subject = 'Commentaire '.$comment->getIsEnabled() ? 'approuvé' : 'refusé';
        $this->mailerService->sendEmail(
            $this->configuration->get('mailer.from_email'),
            $comment->getAuthor()->getEmail(),
            $subject,
            $this->twig->render('emails/comment_status.html.twig', [
                'comment' => $comment,
            ])
        );

        $this->sendJsonResponse(['success' => $success]);
    }

    public function togglePostStatus(int $postId)
    {
        $this->securityHelper->denyAccessUnlessAdmin();

        $post = $this->postManager->find($postId);
        if (null === $post) {
            $this->sendJsonResponse(['error' => 'Article non trouvé.'], 404);

            return;
        }
        $post->setIsEnabled(!$post->getIsEnabled());
        $success = $this->postManager->updateIsEnabled($post);

        $this->sendJsonResponse(['success' => $success]);
    }

    public function promoteUser(int $userId)
    {
        $this->securityHelper->denyAccessUnlessAdmin();

        $user = $this->userManager->find($userId);
        if (null === $user) {
            $this->sendJsonResponse(['error' => 'Utilisateur non trouvé.'], 404);

            return;
        }

        $currentRole = $user->getRole();
        $newRole = 'ROLE_ADMIN' === $currentRole ? 'ROLE_USER' : 'ROLE_ADMIN';
        $user->setRole($newRole);
        $success = $this->userManager->updateRole($user);

        $this->sendJsonResponse(['success' => $success]);
    }

    private function sendJsonResponse(array $data, int $statusCode = 200)
    {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data);
    }
}
