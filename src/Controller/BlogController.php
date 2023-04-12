<?php

declare(strict_types=1);

namespace App\Controller;

use App\Config\DatabaseConnexion;
use App\Helper\SecurityHelper;
use App\Helper\StringHelper;
use App\Helper\TwigHelper;
use App\Manager\CategoryManager;
use App\Manager\PostManager;
use App\Manager\TagManager;
use App\Manager\UserManager;
use Tracy\Debugger;

class BlogController extends TwigHelper
{
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
    }

    /**
     * Display the blog index page.
     *
     * @param null  $message
     * @param mixed $session
     */
    public function blogIndex($message = null)
    {
        // If the user is logged then $message is set to 'You are logged in'
        // If the user is not logged then $message is set to 'You are not logged in'

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
        Debugger::barDump($data);

        $twig = new TwigHelper();
        $twig->render('pages/blog/index.html.twig', $data);
    }

    /**
     * Display the blog post page.
     *
     * @param null  $message
     * @param mixed $slug
     */
    public function blogPost($slug, $message = null)
    {
        $sh = new StringHelper();
        $url = $_SERVER['REQUEST_URI'];
        $slug = $sh->getLastUrlPart($url);
        $post = $this->postManager->findBy(['slug' => $slug]);
        $post = (object) $post[0];
        $author = $this->userManager->findBy(['username' => $post->getAuthor()]);
        $data = [
            'title' => 'MyBlog - Blog',
            'route' => 'blog',
            'message' => $message,
            'post' => $post,
            'author' => $author,
            'tags' => $this->tags,
            'categories' => $this->popularCategories,
            'recentPosts' => $this->recentPosts,
            'session' => $this->session,
        ];
        $twig = new TwigHelper();
        $twig->render('pages/blog/post.html.twig', $data);
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
        $sh = new StringHelper();
        $url = $_SERVER['REQUEST_URI'];
        $categorySlug = $sh->getLastUrlPart($url);
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

        $twig = new TwigHelper();
        $twig->render('pages/blog/index.html.twig', $data);
    }

    public function blogTag($tagSlug, $message = null)
    {
        $sh = new StringHelper();
        $url = $_SERVER['REQUEST_URI'];
        $tagSlug = $sh->getLastUrlPart($url);
        $posts = $this->postManager->findBy(['tag' => "{$tagSlug}"]);

        $data = [
            'title' => 'MyBlog - Blog',
            'route' => 'blog',
            'searchType' => 'Categorie',
            'search' => $tagSlug,
            'message' => $message,
            'posts' => $posts,
            'tags' => $this->tags,
            'categories' => $this->popularCategories,
            'recentPosts' => $this->recentPosts,
            'session' => $this->session,
        ];

        $twig = new TwigHelper();
        $twig->render('pages/blog/index.html.twig', $data);
    }

    public function blogAuthor($username, $message = null)
    {
        $sh = new StringHelper();
        $url = $_SERVER['REQUEST_URI'];
        $username = $sh->getLastUrlPart($url);
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

        $twig = new TwigHelper();
        $twig->render('pages/blog/index.html.twig', $data);
    }

    /**
     * Return the posts made in the last 30 days.
     *
     * @param [type] $date
     * @param [type] $message
     */
    public function blogDate($date, $message = null)
    {
        $sh = new StringHelper();
        $url = $_SERVER['REQUEST_URI'];
        $date = $sh->getLastUrlPart($url);
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

        $twig = new TwigHelper();
        $twig->render('pages/blog/index.html.twig', $data);
    }
}
