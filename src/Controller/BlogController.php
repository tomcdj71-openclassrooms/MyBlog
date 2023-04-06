<?php

declare(strict_types=1);

namespace App\Controller;

use App\Config\DatabaseConnexion;
use App\Helper\StringHelper;
use App\Helper\TwigHelper;
use App\Manager\CategoryManager;
use App\Manager\PostManager;
use App\Manager\TagManager;
use App\Manager\UserManager;

class BlogController extends TwigHelper
{
    protected $postManager;
    protected $tagManager;
    protected $categoryManager;
    protected $userManager;

    public function __construct()
    {
        $db = new DatabaseConnexion();
        $this->postManager = new PostManager($db);
        $this->tagManager = new TagManager($db);
        $this->categoryManager = new CategoryManager($db);
        $this->userManager = new UserManager($db);
    }

    /**
     * Display the blog index page.
     *
     * @param null $message
     */
    public function blogIndex($message = null)
    {
        $posts = $this->postManager->findAll();
        $tags = $this->tagManager->findAll();
        $categories = $this->categoryManager->findPopularCategories();
        $tags = $this->tagManager->findAll();
        $date = date('Y-m-d', strtotime('-30 days'));
        $recentPosts = $this->postManager->findBy(['recent' => $date, 'limit' => 5, 'order' => 'ASC']);
        $data = [
            'title' => 'MyBlog - Blog',
            'message' => $message,
            'route' => 'blog',
            'posts' => $posts,
            'tags' => $tags,
            'categories' => $categories,
            'recentPosts' => $recentPosts,
        ];

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
        $posts = $this->postManager->findBy(['slug' => $slug]);
        $post = $posts ? $posts[0] : null;
        $username = $post->getAuthor();
        $author = $this->userManager->findBy(['username' => $username]);
        $tags = $this->tagManager->findAll();
        $categories = $this->categoryManager->findPopularCategories();
        $tags = $this->tagManager->findAll();
        $date = date('Y-m-d H:i:s', strtotime('-30 days'));
        $recentPosts = $this->postManager->findBy(['recent' => $date, 'limit' => 5, 'order' => 'DESC']);

        $data = [
            'title' => 'MyBlog - Blog',
            'message' => $message,
            'route' => 'blog',
            'post' => $post,
            'tags' => $tags,
            'categories' => $categories,
            'recentPosts' => $recentPosts,
            'postAuthor' => $author,
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
        $tags = $this->tagManager->findAll();
        $categories = $this->categoryManager->findPopularCategories();
        $tags = $this->tagManager->findAll();
        $date = date('Y-m-d H:i:s', strtotime('-30 days'));
        $recentPosts = $this->postManager->findBy(['recent' => $date, 'limit' => 5, 'order' => 'DESC']);

        $data = [
            'title' => 'MyBlog - Blog',
            'message' => $message,
            'route' => 'blog',
            'posts' => $posts,
            'tags' => $tags,
            'categories' => $categories,
            'recentPosts' => $recentPosts,
            'search' => $categorySlug,
            'searchType' => 'Categorie',
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
        $tags = $this->tagManager->findAll();
        $categories = $this->categoryManager->findPopularCategories();
        $tags = $this->tagManager->findAll();
        $date = date('Y-m-d H:i:s', strtotime('-30 days'));
        $recentPosts = $this->postManager->findBy(['recent' => $date, 'limit' => 5, 'order' => 'DESC']);

        $data = [
            'title' => 'MyBlog - Blog',
            'message' => $message,
            'route' => 'blog',
            'posts' => $posts,
            'tags' => $tags,
            'categories' => $categories,
            'recentPosts' => $recentPosts,
            'search' => $tagSlug,
            'searchType' => 'Categorie',
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
        $json = json_encode($author);
        $postAuthor = json_decode($json, true);
        $authorId = $postAuthor['id'];
        $posts = $this->postManager->findBy(['author' => $authorId]);
        $tags = $this->tagManager->findAll();
        $categories = $this->categoryManager->findPopularCategories();
        $tags = $this->tagManager->findAll();
        $date = date('Y-m-d H:i:s', strtotime('-30 days'));
        $recentPosts = $this->postManager->findBy(['recent' => $date, 'limit' => 5, 'order' => 'DESC']);

        $data = [
            'title' => 'MyBlog - Blog',
            'message' => $message,
            'route' => 'blog',
            'posts' => $posts,
            'tags' => $tags,
            'categories' => $categories,
            'recentPosts' => $recentPosts,
            'search' => $username,
            'searchType' => 'Auteur',
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
        $tags = $this->tagManager->findAll();
        $categories = $this->categoryManager->findPopularCategories();
        $tags = $this->tagManager->findAll();
        $dateNow = date('Y-m-d H:i:s', strtotime('-30 days'));
        $recentPosts = $this->postManager->findBy(['recent' => $dateNow, 'limit' => 5, 'order' => 'DESC']);

        $data = [
            'title' => 'MyBlog - Blog',
            'message' => $message,
            'route' => 'blog',
            'posts' => $posts,
            'tags' => $tags,
            'categories' => $categories,
            'recentPosts' => $recentPosts,
            'search' => 'Postés entre le '.date('d-m-Y', strtotime($fromDate)).' et le '.date('d-m-Y', strtotime($date)),
            'searchType' => 'Date',
        ];

        $twig = new TwigHelper();
        $twig->render('pages/blog/index.html.twig', $data);
    }
}
