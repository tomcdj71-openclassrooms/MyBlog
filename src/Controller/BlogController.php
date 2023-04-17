<?php

declare(strict_types=1);

namespace App\Controller;

use App\Config\DatabaseConnexion;
use App\Helper\SecurityHelper;
use App\Helper\StringHelper;
use App\Helper\TwigHelper;
use App\Manager\CategoryManager;
use App\Manager\CommentManager;
use App\Manager\PostManager;
use App\Manager\TagManager;
use App\Manager\UserManager;
use App\Validator\CommentFormValidator;
use Tracy\Debugger;

class BlogController extends TwigHelper
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
    private $posts;
    private $date;
    private $recentPosts;
    private $commentManager;
    private $stringHelper;

    public function __construct()
    {
        $db = new DatabaseConnexion();
        $this->postManager = new PostManager($db);
        $this->tagManager = new TagManager($db);
        $this->categoryManager = new CategoryManager($db);
        $this->userManager = new UserManager($db);
        $this->securityHelper = new SecurityHelper($db);
        $this->session = $this->securityHelper->getSession();
        $this->popularCategories = $this->categoryManager->findPopularCategories();
        $this->tags = $this->tagManager->findAll();
        $this->posts = $this->postManager->findAll();
        $this->date = date('Y-m-d', strtotime('-30 days'));
        $this->recentPosts = $this->postManager->findBy(['recent' => $this->date, 'limit' => 5, 'order' => 'ASC']);
        $this->commentManager = new CommentManager($db);
        $this->twig = new TwigHelper();
        $this->stringHelper = new StringHelper();
    }

    /**
     * Display the blog index page.
     *
     * @param null  $message
     * @param mixed $session
     */
    public function blogIndex($message = null)
    {
        $isAuthenticated = $this->securityHelper->isAuthenticated();
        $message = $isAuthenticated ? 'You are logged in' : 'You are not logged in';
        $data = [
            'title' => 'MyBlog - Blog',
            'route' => 'blog',
            'message' => $message,
            'posts' => $this->posts,
            'tags' => $this->tags,
            'categories' => $this->popularCategories,
            'recentPosts' => $this->recentPosts,
            'session' => $this->session,
        ];

        $this->twig->render('pages/blog/index.html.twig', $data);
    }

    /**
     * Display the blog post page.
     *
     * @param null  $message
     * @param mixed $slug
     */
    public function blogPost($slug, $message = null)
    {
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
        if ('POST' === $_SERVER['REQUEST_METHOD'] && isset($_POST['content'], $_POST['csrf_token'])) {
            $csrf_token = $_POST['csrf_token'];
            if ($this->securityHelper->checkCsrfToken('comment', $csrf_token)) {
                if (isset($_POST['parentId'])) {
                    $postData = [
                        'content' => $_POST['content'],
                        'author_id' => $user->getId(),
                        'post_id' => $post->getId(),
                        'parent_id' => $_POST['parentId'],
                        'csrf_token' => $_POST['csrf_token'],
                    ];
                } else {
                    $postData = [
                        'content' => $_POST['content'],
                        'author_id' => $user->getId(),
                        'post_id' => $post->getId(),
                        'csrf_token' => $_POST['csrf_token'],
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
                    $data = [
                        'title' => 'MyBlog - Blog',
                        'route' => 'blog',
                        'loggedUser' => $user,
                        'message' => $message,
                        'post' => $post,
                        'author' => $author,
                        'tags' => $this->tags,
                        'categories' => $this->popularCategories,
                        'recentPosts' => $this->recentPosts,
                        'session' => $this->session,
                        'csrf_token' => $csrf_token,
                    ];

                    return $this->blogPost($slug, $message, $data, $csrf_token);
                }
                $message = 'Your comment has not been added';
            } else {
                $message = 'Invalid CSRF token';
            }
        }
        $csrf_token = $this->securityHelper->generateCsrfToken('comment');
        $data = [
            'title' => 'MyBlog - Blog',
            'route' => 'blog',
            'loggedUser' => $user,
            'message' => $message,
            'post' => $post,
            'author' => $author,
            'tags' => $this->tags,
            'categories' => $this->popularCategories,
            'recentPosts' => $this->recentPosts,
            'session' => $this->session,
            'csrf_token' => $csrf_token,
        ];
        Debugger::barDump($post);

        $this->twig->render('pages/blog/post.html.twig', $data);
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
        $url = $_SERVER['REQUEST_URI'];
        $categorySlug = $this->stringHelper->getLastUrlPart($url);
        $posts = $this->postManager->findBy(['category' => "{$categorySlug}"]);

        $data = [
            'title' => 'MyBlog - Blog',
            'route' => 'blog',
            'searchType' => 'Categorie',
            'search' => $categorySlug,
            'message' => $message,
            'posts' => $posts,
            'tags' => $this->tags,
            'categories' => $this->popularCategories,
            'recentPosts' => $this->recentPosts,
            'session' => $this->session,
        ];

        $this->twig->render('pages/blog/index.html.twig', $data);
    }

    public function blogTag($tagSlug, $message = null)
    {
        $url = $_SERVER['REQUEST_URI'];
        $tagSlug = $this->stringHelper->getLastUrlPart($url);
        $posts = $this->postManager->findBy(['tag' => "{$tagSlug}"]);

        $data = [
            'title' => 'MyBlog - Blog',
            'route' => 'blog',
            'searchType' => 'Tag',
            'search' => $tagSlug,
            'message' => $message,
            'posts' => $posts,
            'tags' => $this->tags,
            'categories' => $this->popularCategories,
            'recentPosts' => $this->recentPosts,
            'session' => $this->session,
        ];

        $this->twig->render('pages/blog/index.html.twig', $data);
    }

    public function blogAuthor($username, $message = null)
    {
        $url = $_SERVER['REQUEST_URI'];
        $username = $this->stringHelper->getLastUrlPart($url);
        $author = $this->userManager->findBy(['username' => $username]);
        $authorId = $author->getId();
        $posts = $this->postManager->findBy(['author' => $authorId]);

        $data = [
            'title' => 'MyBlog - Blog',
            'route' => 'blog',
            'searchType' => 'Auteur',
            'search' => $username,
            'message' => $message,
            'posts' => $posts,
            'tags' => $this->tags,
            'categories' => $this->popularCategories,
            'recentPosts' => $this->recentPosts,
            'session' => $this->session,
        ];

        $this->twig->render('pages/blog/index.html.twig', $data);
    }

    /**
     * Return the posts made in the last 30 days.
     *
     * @param [type] $date
     * @param [type] $message
     */
    public function blogDate($date, $message = null)
    {
        $url = $_SERVER['REQUEST_URI'];
        $date = $this->stringHelper->getLastUrlPart($url);
        $fromDate = date('Y-m-d', strtotime($date.' -15 days'));
        $posts = $this->postManager->findBy(['from_date' => $fromDate, 'to_date' => $date]);

        $data = [
            'title' => 'MyBlog - Blog',
            'route' => 'blog',
            'searchType' => 'Date',
            'search' => 'Postés entre le '.date('d-m-Y', strtotime($fromDate)).' et le '.date('d-m-Y', strtotime($date)),
            'message' => $message,
            'posts' => $posts,
            'tags' => $this->tags,
            'categories' => $this->popularCategories,
            'recentPosts' => $this->recentPosts,
            'session' => $this->session,
        ];

        $this->twig->render('pages/blog/index.html.twig', $data);
    }
}
