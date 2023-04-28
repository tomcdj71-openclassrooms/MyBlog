<?php

declare(strict_types=1);

namespace App\Controller;

use App\DependencyInjection\Container;
use App\Helper\StringHelper;
use App\Manager\CategoryManager;
use App\Manager\CommentManager;
use App\Manager\PostManager;
use App\Manager\TagManager;
use App\Service\CommentService;
use Tracy\Debugger;

class BlogController extends AbstractController
{
    private CategoryManager $categoryManager;
    private PostManager $postManager;
    private TagManager $tagManager;
    private StringHelper $stringHelper;
    private CommentManager $commentManager;
    private CommentService $commentService;

    public function __construct(Container $container)
    {
        parent::__construct($container);
        $container->injectProperties($this);
    }

    /**
     * Display the blog index page.
     *
     * @param null $message
     */
    public function blogIndex($message = null)
    {
        $data = $this->resetData();
        $page = $this->serverRequest->getQuery('page') ? intval($this->serverRequest->getQuery('page')) : 1;
        $limit = 10;
        $totalPosts = $this->postManager->countAll();
        $totalPages = ceil($totalPosts / $limit);
        $posts = $this->postManager->findAll($page, $limit);
        $data['message'] = $message;
        $data['posts'] = $posts['posts'];
        $data['currentPage'] = $page;
        $data['totalPages'] = $totalPages;
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
        $user = $this->securityHelper->getUser();
        if (null === $user) {
            $user = null;
        }
        if ('POST' === $this->serverRequest->getRequestMethod() && $this->serverRequest->getPost('content') && $this->serverRequest->getPost('csrfToken')) {
            $postData = [
                'content' => $this->serverRequest->getPost('content'),
                'post_id' => $post,
                'user_id' => $this->session->getUserFromSession()->getId(),
                'parent_id' => $this->serverRequest->getPost('parentId'),
                'csrfToken' => $this->serverRequest->getPost('csrfToken'),
            ];
            list($errors, $message) = $this->commentService->handleCommentPostRequest($post, $postData);
            if (empty($errors)) {
                $csrfToken = $this->securityHelper->generateCsrfToken('comment');
                $data['title'] = 'MyBlog - Blog Post';
                $data['route'] = 'blog';
                $data['user'] = $user;
                $data['message'] = 'Commentaire posté avec succès !';
                $data['errors'] = $errors;
                $data['csrfToken'] = $csrfToken;
                $data['post'] = $post;
                $data['comments'] = $this->commentManager->findAllByPost($post->getId());
                $data['loggedUser'] = $user;
                $data['session'] = $this->session;

                return $this->twig->render('pages/blog/post.html.twig', $data);
            }
            $errors = array_map('strval', $errors);
            $message = implode(', ', $errors);
        }
        $csrfToken = $this->securityHelper->generateCsrfToken('comment');
        $data['title'] = 'MyBlog - Blog Post';
        $data['route'] = 'blog';
        $data['user'] = $user;
        $data['message'] = $message;
        $data['csrfToken'] = $csrfToken;
        $data['post'] = $post;
        $data['comments'] = $this->commentManager->findAllByPost($post->getId());
        $data['loggedUser'] = $user;
        $data['session'] = $this->session;
        Debugger::barDump($data);
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
