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
        $recentPosts = $this->postManager->findRecentPosts();
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
        $post = $this->postManager->findBySlug($slug);
        $author = $this->userManager->findByUsername($post->getAuthor());
        $tags = $this->tagManager->findAll();
        $categories = $this->categoryManager->findPopularCategories();
        $recentPosts = $this->postManager->findRecentPosts();
        $data = [
            'title' => 'MyBlog - Blog',
            'message' => $message,
            'route' => 'blog',
            'post' => $post,
            'tags' => $tags,
            'categories' => $categories,
            'recentPosts' => $recentPosts,
            'author' => $author,
        ];

        $twig = new TwigHelper();
        $twig->render('pages/blog/post.html.twig', $data);
    }

    /**
     * Display the blog category page.
     *
     * @param null  $message
     * @param mixed $slug
     */
    public function blogCategory($slug, $message = null)
    {
        $sh = new StringHelper();
        $url = $_SERVER['REQUEST_URI'];
        $slug = $sh->getLastUrlPart($url);
        $posts = $this->postManager->findPostsWithCategory($slug);
        $tags = $this->tagManager->findAll();
        $categories = $this->categoryManager->findPopularCategories();
        $recentPosts = $this->postManager->findRecentPosts();
        $data = [
            'title' => 'MyBlog - Blog',
            'message' => $message,
            'route' => 'blog',
            'posts' => $posts,
            'tags' => $tags,
            'categories' => $categories,
            'recentPosts' => $recentPosts,
            'search' => $slug,
            'searchType' => 'Categorie',
        ];

        $twig = new TwigHelper();
        $twig->render('pages/blog/index.html.twig', $data);
    }

    public function blogTag($slug, $message = null)
    {
        $sh = new StringHelper();
        $url = $_SERVER['REQUEST_URI'];
        $slug = $sh->getLastUrlPart($url);
        $posts = $this->postManager->findPostsWithTag($slug);
        $tags = $this->tagManager->findAll();
        $categories = $this->categoryManager->findPopularCategories();
        $recentPosts = $this->postManager->findRecentPosts();
        $data = [
            'title' => 'MyBlog - Blog',
            'message' => $message,
            'route' => 'blog',
            'posts' => $posts,
            'tags' => $tags,
            'categories' => $categories,
            'recentPosts' => $recentPosts,
            'search' => $slug,
            'searchType' => 'Tag',
        ];

        $twig = new TwigHelper();
        $twig->render('pages/blog/index.html.twig', $data);
    }

    public function blogAuthor($author, $message = null)
    {
        $sh = new StringHelper();
        $url = $_SERVER['REQUEST_URI'];
        $author = $sh->getLastUrlPart($url);
        $posts = $this->postManager->findPostsWithAuthor($author);
        $tags = $this->tagManager->findAll();
        $categories = $this->categoryManager->findPopularCategories();
        $recentPosts = $this->postManager->findRecentPosts();
        $data = [
            'title' => 'MyBlog - Blog',
            'message' => $message,
            'route' => 'blog',
            'posts' => $posts,
            'tags' => $tags,
            'categories' => $categories,
            'recentPosts' => $recentPosts,
            'search' => $author,
            'searchType' => 'Auteur',
        ];

        $twig = new TwigHelper();
        $twig->render('pages/blog/index.html.twig', $data);
    }

    public function blogDate($date, $message = null)
    {
        $sh = new StringHelper();
        $url = $_SERVER['REQUEST_URI'];
        $date = $sh->getLastUrlPart($url);
        $date = date('Y-m-d', strtotime($date));
        $posts = $this->postManager->findPostsPostedAt($date);
        $tags = $this->tagManager->findAll();
        $categories = $this->categoryManager->findPopularCategories();
        $recentPosts = $this->postManager->findRecentPosts();
        $data = [
            'title' => 'MyBlog - Blog',
            'message' => $message,
            'route' => 'blog',
            'posts' => $posts,
            'tags' => $tags,
            'categories' => $categories,
            'recentPosts' => $recentPosts,
            'search' => $date,
            'searchType' => 'Date',
        ];

        $twig = new TwigHelper();
        $twig->render('pages/blog/index.html.twig', $data);
    }
}
