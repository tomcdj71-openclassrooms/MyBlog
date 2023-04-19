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
use App\Router\Session;
use App\Validator\CommentFormValidator;

class BlogController
{
    protected TwigHelper $twig;
    private CategoryManager $categoryManager;
    private PostManager $postManager;
    private TagManager $tagManager;
    private UserManager $userManager;
    private SecurityHelper $securityHelper;
    private StringHelper $stringHelper;
    private CommentManager $commentManager;
    private Session $session;

    public function __construct(Container $container)
    {
        $container->injectProperties($this);
    }

    /**
     * Display the blog index page.
     *
     * @param null  $message
     * @param mixed $session
     */
    public function blogIndex($message = null)
    {
        $data = $this->resetData();
        $posts = $this->postManager->findAll();
        $data['message'] = $message;
        $data['posts'] = $posts;
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
        $data = $this->resetData();
        $url = $_SERVER['REQUEST_URI'];
        $slug = $this->stringHelper->getLastUrlPart($url);
        $post = $this->postManager->findOneBy(['slug' => $slug, 'limit' => 1]);
        if (null === $post) {
            header('Location: /404');

            exit;
        }
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
                    $data['csrf_token'] = $csrf_token;
                    $data['message'] = $message;
                    $data['post'] = $post;
                    $data['author'] = $author;
                    $data['loggedUser'] = $user;

                    return $this->blogPost($slug, $message, $data);
                }
                $message = 'Your comment has not been added';
            } else {
                $message = 'Invalid CSRF token';
            }
        }
        $csrf_token = $this->securityHelper->generateCsrfToken('comment');
        $data['csrf_token'] = $csrf_token;
        $data['message'] = $message;
        $data['post'] = $post;
        $data['author'] = $author;
        $data['loggedUser'] = $user;

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
        $data = $this->resetData();
        $url = $_SERVER['REQUEST_URI'];
        $categorySlug = $this->stringHelper->getLastUrlPart($url);
        $posts = $this->postManager->findBy(['category' => "{$categorySlug}"]);
        $data['searchType'] = 'Catégorie';
        $data['search'] = $categorySlug;
        $data['message'] = $message;
        $data['posts'] = $posts;
        $this->twig->render('pages/blog/index.html.twig', $data);
    }

    public function blogTag($tagSlug, $message = null)
    {
        $data = $this->resetData();
        $url = $_SERVER['REQUEST_URI'];
        $tagSlug = $this->stringHelper->getLastUrlPart($url);
        $posts = $this->postManager->findBy(['tag' => "{$tagSlug}"]);
        $data['searchType'] = 'Tag';
        $data['search'] = $tagSlug;
        $data['message'] = $message;
        $data['posts'] = $posts;
        $this->twig->render('pages/blog/index.html.twig', $data);
    }

    public function blogAuthor($username, $message = null)
    {
        $data = $this->resetData();
        $url = $_SERVER['REQUEST_URI'];
        $username = $this->stringHelper->getLastUrlPart($url);
        $author = $this->userManager->findBy(['username' => $username]);
        $authorId = $author->getId();
        $posts = $this->postManager->findBy(['author' => $authorId]);
        $data['searchType'] = 'Auteur';
        $data['search'] = $username;
        $data['message'] = $message;
        $data['posts'] = $posts;
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
        $data = $this->resetData();
        $url = $_SERVER['REQUEST_URI'];
        $date = $this->stringHelper->getLastUrlPart($url);
        $fromDate = date('Y-m-d', strtotime($date.' -15 days'));
        $posts = $this->postManager->findBy(['from_date' => $fromDate, 'to_date' => $date]);
        $data['searchType'] = 'Date';
        $data['search'] = 'Postés entre le '.date('d-m-Y', strtotime($fromDate)).' et le '.date('d-m-Y', strtotime($date));
        $data['message'] = $message;
        $data['posts'] = $posts;
        $this->twig->render('pages/blog/index.html.twig', $data);
    }

    private function resetData()
    {
        $date = date('Y-m-d', strtotime('-30 days'));

        return [
            'title' => 'MyBlog - Blog',
            'route' => 'blog',
            'message' => '',
            'posts' => [],
            'tags' => $this->tagManager->findAll(),
            'categories' => $this->categoryManager->findByPopularity(),
            'recentPosts' => $this->postManager->findBy(['recent' => $date, 'limit' => 5, 'order' => 'ASC']),
            'session' => $this->session,
        ];
    }
}
