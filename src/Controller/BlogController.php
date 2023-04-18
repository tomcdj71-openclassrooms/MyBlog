<?php

declare(strict_types=1);

namespace App\Controller;

use App\DependencyInjection\Container;
use App\Helper\SecurityHelper;
use App\Helper\StringHelper;
use App\Helper\TwigHelper;
use App\Manager\CategoryManager;
use App\Manager\CommentManager;
use App\Manager\PostManager;
use App\Manager\TagManager;
use App\Manager\UserManager;
use App\Middleware\AuthenticationMiddleware;
use App\Validator\CommentFormValidator;

class BlogController
{
    protected $twig;
    private $postManager;
    private $tagManager;
    private $categoryManager;
    private $userManager;
    private $securityHelper;
    private $session;
    private $popularCategories;
    private $tags;
    private $date;
    private $recentPosts;
    private $commentManager;
    private $stringHelper;
    private $authMiddleware;
    private $data;

    public function __construct(Container $container)
    {
        $this->categoryManager = $container->get(CategoryManager::class);
        $this->postManager = $container->get(PostManager::class);
        $this->tagManager = $container->get(TagManager::class);
        $this->userManager = $container->get(UserManager::class);
        $this->securityHelper = $container->get(SecurityHelper::class);
        $this->twig = $container->get(TwigHelper::class);
        $this->stringHelper = $container->get(StringHelper::class);
        $this->commentManager = $container->get(CommentManager::class);
        $this->authMiddleware = $container->get(AuthenticationMiddleware::class);
    }

    /**
     * Display the blog index page.
     *
     * @param null  $message
     * @param mixed $session
     */
    public function blogIndex($message = null)
    {
        $this->resetData();
        $posts = $this->postManager->findAll();
        $isAuthenticated = $this->securityHelper->isAuthenticated();
        $message = $isAuthenticated ? 'You are logged in' : 'You are not logged in';
        $this->data['message'] = $message;
        $this->data['posts'] = $posts;
        $this->twig->render('pages/blog/index.html.twig', $this->data);
    }

    /**
     * Display the blog post page.
     *
     * @param null  $message
     * @param mixed $slug
     */
    public function blogPost($slug, $message = null)
    {
        $this->resetData();
        $url = $_SERVER['REQUEST_URI'];
        $slug = $this->stringHelper->getLastUrlPart($url);
        $post = $this->postManager->findOneBy(['slug' => $slug, 'limit' => 1]);
        $author = $this->userManager->findBy(['username' => $post->getAuthor()]);
        $comments = $post->getComments();
        foreach ($comments as $comment) {
            $commentAuthor = $this->userManager->find($comment->getAuthor());
            $comment->author = $commentAuthor;
        }
        if (null === $this->securityHelper->getUser()) {
            $user = null;
        } else {
            $user = $this->securityHelper->getUser();
        }
        $validator = new CommentFormValidator($this->securityHelper);
        if ('POST' === $_SERVER['REQUEST_METHOD'] && filter_input(INPUT_POST, 'content') && filter_input(INPUT_POST, 'csrf_token')) {
            $csrf_token = filter_input(INPUT_POST, 'csrf_token');
            if ($this->securityHelper->checkCsrfToken('comment', $csrf_token)) {
                if (filter_input(INPUT_POST, 'parentId')) {
                    $postData = [
                        'content' => filter_input(INPUT_POST, 'content'),
                        'author_id' => $user->getId(),
                        'post_id' => $post->getId(),
                        'parent_id' => filter_input(INPUT_POST, 'parentId'),
                        'csrf_token' => filter_input(INPUT_POST, 'csrf_token'),
                    ];
                } else {
                    $postData = [
                        'content' => filter_input(INPUT_POST, 'content'),
                        'author_id' => $user->getId(),
                        'post_id' => $post->getId(),
                        'csrf_token' => filter_input(INPUT_POST, 'csrf_token'),
                    ];
                }
                $validation = $validator->validate($postData);
                if ($validation['valid']) {
                    $date = new \DateTime();
                    $postData['created_at'] = $date->format('Y-m-d H:i:s');
                    $postData['is_enabled'] = ('ROLE_ADMIN' === $user->getRole()) ? 1 : 0;
                    $this->commentManager->create($postData);
                    $message = 'Your comment has been added';
                    // generate new CSRF token to prevent multiple submissions
                    $csrf_token = $this->securityHelper->generateCsrfToken('comment');
                    $this->data['csrf_token'] = $csrf_token;
                    $this->data['message'] = $message;
                    $this->data['post'] = $post;
                    $this->data['author'] = $author;
                    $this->data['loggedUser'] = $user;

                    return $this->blogPost($slug, $message, $this->data);
                }
                $message = 'Your comment has not been added';
            } else {
                $message = 'Invalid CSRF token';
            }
        }
        $csrf_token = $this->securityHelper->generateCsrfToken('comment');
        $this->data['csrf_token'] = $csrf_token;
        $this->data['message'] = $message;
        $this->data['post'] = $post;
        $this->data['author'] = $author;
        $this->data['loggedUser'] = $user;

        $this->twig->render('pages/blog/post.html.twig', $this->data);
    }

    /**
     * Display the blog category page.
     *
     * @param null  $message
     * @param mixed $slug
     * @param mixed $categorySlug
     */
    public function blogCategory($categorySlug, $message = null)
    {
        $this->resetData();
        $url = $_SERVER['REQUEST_URI'];
        $categorySlug = $this->stringHelper->getLastUrlPart($url);
        $posts = $this->postManager->findBy(['category' => "{$categorySlug}"]);
        $this->data['searchType'] = 'Catégorie';
        $this->data['search'] = $categorySlug;
        $this->data['message'] = $message;
        $this->data['posts'] = $posts;
        $this->twig->render('pages/blog/index.html.twig', $this->data);
    }

    public function tag($tagSlug, $message = null)
    {
        $this->resetData();
        $url = $_SERVER['REQUEST_URI'];
        $tagSlug = $this->stringHelper->getLastUrlPart($url);
        $posts = $this->postManager->findBy(['tag' => "{$tagSlug}"]);
        $this->data['searchType'] = 'Tag';
        $this->data['search'] = $tagSlug;
        $this->data['message'] = $message;
        $this->data['posts'] = $posts;
        $this->twig->render('pages/blog/index.html.twig', $this->data);
    }

    public function blogAuthor($username, $message = null)
    {
        $this->resetData();
        $url = $_SERVER['REQUEST_URI'];
        $username = $this->stringHelper->getLastUrlPart($url);
        $author = $this->userManager->findBy(['username' => $username]);
        $authorId = $author->getId();
        $posts = $this->postManager->findBy(['author' => $authorId]);
        $this->data['searchType'] = 'Auteur';
        $this->data['search'] = $username;
        $this->data['message'] = $message;
        $this->data['posts'] = $posts;
        $this->twig->render('pages/blog/index.html.twig', $this->data);
    }

    /**
     * Return the posts made in the last 30 days.
     *
     * @param [type] $date
     * @param [type] $message
     */
    public function blogDate($date, $message = null)
    {
        $this->resetData();
        $url = $_SERVER['REQUEST_URI'];
        $date = $this->stringHelper->getLastUrlPart($url);
        $fromDate = date('Y-m-d', strtotime($date.' -15 days'));
        $posts = $this->postManager->findBy(['from_date' => $fromDate, 'to_date' => $date]);
        $this->data['searchType'] = 'Date';
        $this->data['search'] = 'Postés entre le '.date('d-m-Y', strtotime($fromDate)).' et le '.date('d-m-Y', strtotime($date));
        $this->data['message'] = $message;
        $this->data['posts'] = $posts;
        $this->twig->render('pages/blog/index.html.twig', $this->data);
    }

    private function resetData()
    {
        $this->data = [
            'title' => 'MyBlog - Blog',
            'route' => 'blog',
            'message' => '',
            'posts' => [],
            'tags' => $this->tagManager->findAll(),
            'categories' => $this->categoryManager->findPopularCategories(),
            'recentPosts' => $this->postManager->findBy(['recent' => $this->date, 'limit' => 5, 'order' => 'ASC']),
            'session' => $this->securityHelper->getSession(),
        ];
    }
}
