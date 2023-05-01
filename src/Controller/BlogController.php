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
    private TagManager $tagManager;
    private StringHelper $stringHelper;
    private CommentManager $commentManager;
    private CommentService $commentService;
    private PostManager $postManager;

    public function __construct(Container $container)
    {
        parent::__construct($container);
        $container->injectProperties($this);
        $this->postManager = $container->get(PostManager::class);
        $this->categoryManager = $container->get(CategoryManager::class);
        $this->tagManager = $container->get(TagManager::class);
    }

    /**
     * Display the blog index page.
     *
     * @param null $message
     */
    public function blogIndex($message = null)
    {
        $page = $this->serverRequest->getQuery('page') ? intval($this->serverRequest->getQuery('page')) : 1;
        $limit = 10;
        $totalPosts = $this->postManager->countAll();
        $totalPages = ceil($totalPosts / $limit);
        $posts = $this->postManager->findAll($page, $limit);

        return $this->twig->render('pages/blog/index.html.twig', [
            'title' => 'MyBlog - Blog',
            'route' => 'blog',
            'user' => $this->securityHelper->getUser(),
            'session' => $this->session,
            'message' => $message,
            'posts' => $posts['posts'],
            'currentPage' => $page,
            'totalPages' => $totalPages,
            'categories' => $this->categoryManager->findByPopularity(),
            'recentPosts' => $this->postManager->findRecentPosts(),
            'tags' => $this->tagManager->findAll(),
        ]);
    }

    /**
     * Display the blog post page.
     *
     * @param null  $message
     * @param mixed $slug
     */
    public function blogPost($slug, $message = null)
    {
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

                return $this->twig->render('pages/blog/post.html.twig', [
                    'title' => 'MyBlog - Blog Post',
                    'route' => 'blog',
                    'user' => $user,
                    'message' => 'Commentaire posté avec succès !',
                    'csrfToken' => $csrfToken,
                    'post' => $post,
                    'errors' => $errors ?? '',
                    'tags' => $this->tagManager->findAll(),
                    'categories' => $this->categoryManager->findByPopularity(),
                    'recentPosts' => $this->postManager->findRecentPosts(),
                    'comments' => $this->commentManager->findAllByPost($post->getId()),
                    'loggedUser' => $user,
                    'session' => $this->session,
                ]);
            }
            $errors = array_map('strval', $errors);
            $message = implode(', ', $errors);
        }
        $csrfToken = $this->securityHelper->generateCsrfToken('comment');
        Debugger::barDump($this->commentManager->findAllByPost($post->getId()));

        return $this->twig->render('pages/blog/post.html.twig', [
            'title' => 'MyBlog - Blog Post',
            'route' => 'blog',
            'user' => $user,
            'message' => $message,
            'csrfToken' => $csrfToken,
            'post' => $post,
            'errors' => $errors ?? '',
            'tags' => $this->tagManager->findAll(),
            'categories' => $this->categoryManager->findByPopularity(),
            'recentPosts' => $this->postManager->findRecentPosts(),
            'comments' => $this->commentManager->findAllByPost($post->getId()),
            'loggedUser' => $user,
            'session' => $this->session,
        ]);
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
        $url = $this->serverRequest->getUri();
        $categorySlug = $this->stringHelper->getLastUrlPart($url);

        return $this->twig->render('pages/blog/index.html.twig', [
            'title' => 'MyBlog - Blog',
            'route' => 'blog',
            'message' => $message,
            'posts' => $this->postManager->findBy('category_slug', $categorySlug),
            'tags' => $this->tagManager->findAll(),
            'categories' => $this->categoryManager->findByPopularity(),
            'recentPosts' => $this->postManager->findRecentPosts(),
            'searchType' => 'Catégorie',
            'search' => $categorySlug,
        ]);
    }

    public function blogTag($tagSlug, $message = null)
    {
        $url = $this->serverRequest->getUri();
        $tagSlug = $this->stringHelper->getLastUrlPart($url);

        return $this->twig->render('pages/blog/index.html.twig', [
            'title' => 'MyBlog - Blog',
            'route' => 'blog',
            'message' => $message,
            'posts' => $this->postManager->findPostsWithTag($tagSlug),
            'tags' => $this->tagManager->findAll(),
            'categories' => $this->categoryManager->findByPopularity(),
            'recentPosts' => $this->postManager->findRecentPosts(),
            'searchType' => 'Tag',
            'search' => $tagSlug,
        ]);
    }

    public function blogAuthor($username, $message = null)
    {
        $url = $this->serverRequest->getUri();
        $username = $this->stringHelper->getLastUrlPart($url);
        $author = $this->userManager->findOneBy(['username' => $username]);
        $authorId = $author->getId();

        return $this->twig->render('pages/blog/index.html.twig', [
            'title' => 'MyBlog - Blog',
            'route' => 'blog',
            'message' => $message,
            'posts' => $this->postManager->findBy('author_id', $authorId),
            'tags' => $this->tagManager->findAll(),
            'categories' => $this->categoryManager->findByPopularity(),
            'recentPosts' => $this->postManager->findRecentPosts(),
            'searchType' => 'Auteur',
            'search' => $username,
        ]);
    }

    /**
     * Return the posts made in the last 30 days.
     *
     * @param [type] $date
     * @param [type] $message
     */
    public function blogDate($date, $message = null)
    {
        $url = $this->serverRequest->getUri();
        $date = $this->stringHelper->getLastUrlPart($url);
        $endDate = new \DateTime($date);
        $startDate = clone $endDate;
        $startDate->modify('-30 days');

        return $this->twig->render('pages/blog/index.html.twig', [
            'title' => 'MyBlog - Blog',
            'route' => 'blog',
            'message' => $message,
            'posts' => $this->postManager->findPostsBetweenDates($startDate, $endDate),
            'tags' => $this->tagManager->findAll(),
            'categories' => $this->categoryManager->findByPopularity(),
            'recentPosts' => $this->postManager->findRecentPosts(),
            'session' => $this->session,
            'searchType' => 'Date',
            'search' => 'Postés entre le '.$startDate->format('d-m-Y').' et le '.$endDate->format('d-m-Y').'.',
        ]);
    }
}
