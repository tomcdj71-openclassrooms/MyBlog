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
use App\Router\ServerRequest;
use App\Router\Session;
use App\Service\CommentService;

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
    private CommentService $commentService;
    private ServerRequest $serverRequest;

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
        $url = $this->serverRequest->getUri();
        $slug = $this->stringHelper->getLastUrlPart($url);
        $post = $this->postManager->findOneBy('slug', $slug);
        if (null === $post) {
            header('Location: /404');
        }
        if (null === $this->securityHelper->getUser()) {
            $user = null;
        } else {
            $user = $this->securityHelper->getUser();
        }
        if ('POST' === $this->serverRequest->getRequestMethod() && filter_input(INPUT_POST, 'content') && filter_input(INPUT_POST, 'csrf_token')) {
            $postData = [
                'content' => filter_input(INPUT_POST, 'content', FILTER_SANITIZE_SPECIAL_CHARS),
                'post_id' => $post,
                'user_id' => $this->session->getUserFromSession()->getId(),
                'parent_id' => filter_input(INPUT_POST, 'parentId', FILTER_SANITIZE_SPECIAL_CHARS),
            ];
            list($errors, $message) = $this->commentService->handleCommentPostRequest($post, $postData);
            if (empty($errors)) {
                $csrf_token = $this->securityHelper->generateCsrfToken('comment');
                $data['title'] = 'MyBlog - Blog Post';
                $data['route'] = 'blog';
                $data['user'] = $user;
                $data['message'] = 'Comment posted successfully';
                $data['errors'] = $errors;
                $data['csrf_token'] = $csrf_token;
                $data['post'] = $post;
                $data['comments'] = $this->commentManager->findAllByPost($post->getId());
                $data['loggedUser'] = $user;
                $data['session'] = $this->session;
                $this->twig->render('pages/blog/post.html.twig', $data);
            }
            $message = implode(', ', $errors); // Combine error messages if there are multiple errors
        }

        $csrf_token = $this->securityHelper->generateCsrfToken('comment');
        $data['title'] = 'MyBlog - Blog Post';
        $data['route'] = 'blog';
        $data['user'] = $user;
        $data['message'] = $message;
        $data['csrf_token'] = $csrf_token;
        $data['post'] = $post;
        $data['comments'] = $this->commentManager->findAllByPost($post->getId());
        $data['loggedUser'] = $user;
        $data['session'] = $this->session;
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
        $url = $this->serverRequest->getUri();
        $categorySlug = $this->stringHelper->getLastUrlPart($url);
        $posts = $this->postManager->findBy('category_slug', $categorySlug);
        $data['searchType'] = 'Catégorie';
        $data['search'] = $categorySlug;
        $data['message'] = $message;
        $data['posts'] = $posts;
        $this->twig->render('pages/blog/index.html.twig', $data);
    }

    public function blogTag($tagSlug, $message = null)
    {
        $data = $this->resetData();
        $url = $this->serverRequest->getUri();
        $tagSlug = $this->stringHelper->getLastUrlPart($url);
        $posts = $this->postManager->findPostsWithTag($tagSlug);
        $data['searchType'] = 'Tag';
        $data['search'] = $tagSlug;
        $data['message'] = $message;
        $data['posts'] = $posts;
        $this->twig->render('pages/blog/index.html.twig', $data);
    }

    public function blogAuthor($username, $message = null)
    {
        $data = $this->resetData();
        $url = $this->serverRequest->getUri();
        $username = $this->stringHelper->getLastUrlPart($url);
        $author = $this->userManager->findOneBy(['username' => $username]);
        $authorId = $author->getId();
        $posts = $this->postManager->findBy('author_id', $authorId);
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
        $url = $this->serverRequest->getUri();
        $date = $this->stringHelper->getLastUrlPart($url);
        $endDate = new \DateTime($date);
        $startDate = clone $endDate;
        $startDate->modify('-30 days');

        $posts = $this->postManager->findPostsBetweenDates($startDate, $endDate);

        $data['searchType'] = 'Date';
        $data['search'] = 'Postés entre le '.$startDate->format('d-m-Y').' et le '.$endDate->format('d-m-Y').'.';
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
            'recentPosts' => $this->postManager->findRecentPosts(),
            'session' => $this->session,
        ];
    }
}
