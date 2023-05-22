<?php

declare(strict_types=1);

namespace App\Controller;

use App\Helper\SecurityHelper;
use App\Helper\TwigHelper;
use App\Manager\CategoryManager;
use App\Manager\PostManager;
use App\Manager\TagManager;
use App\Manager\UserManager;
use App\Router\Request;
use App\Router\ServerRequest;
use App\Router\Session;
use App\Service\CsrfTokenService;
use App\Service\PostService;

class AdminController extends AbstractController
{
    private TagManager $tagManager;
    private CategoryManager $categoryManager;
    private PostService $postService;
    private CsrfTokenService $csrfTokenService;
    private PostManager $postManager;

    public function __construct(
        TwigHelper $twig,
        Session $session,
        ServerRequest $serverRequest,
        SecurityHelper $securityHelper,
        UserManager $userManager,
        Request $request,
        CategoryManager $categoryManager,
        TagManager $tagManager,
        PostService $postService,
        CsrfTokenService $csrfTokenService,
        PostManager $postManager
    ) {
        parent::__construct($twig, $session, $serverRequest, $securityHelper, $userManager, $request);
        $this->tagManager = $tagManager;
        $this->categoryManager = $categoryManager;
        $this->postService = $postService;
        $this->csrfTokenService = $csrfTokenService;
        $this->postManager = $postManager;
    }

    public function index()
    {
        $this->securityHelper->denyAccessUnlessAdmin();

        return $this->twig->render('pages/admin/pages/index.html.twig', [
            'user' => $this->securityHelper->getUser(),
        ]);
    }

    public function categories()
    {
        $this->securityHelper->denyAccessUnlessAdmin();

        return $this->twig->render('pages/admin/pages/category_admin.html.twig', [
            'user' => $this->securityHelper->getUser(),
        ]);
    }

    public function comments()
    {
        $this->securityHelper->denyAccessUnlessAdmin();

        return $this->twig->render('pages/admin/pages/comment_admin.html.twig', [
            'user' => $this->securityHelper->getUser(),
        ]);
    }

    public function posts()
    {
        $this->securityHelper->denyAccessUnlessAdmin();
        $data = [
            'message' => $this->session->flash('message', ''),
            'postSlug' => $this->session->flash('postSlug', ''),
            'formData' => $this->session->flash('formData', ''),
        ];

        return $this->twig->render('pages/admin/pages/post_admin.html.twig', [
            'user' => $this->securityHelper->getUser(),
            'data' => $data,
        ]);
    }

    public function tags()
    {
        $this->securityHelper->denyAccessUnlessAdmin();

        return $this->twig->render('pages/admin/pages/tag_admin.html.twig', [
            'user' => $this->securityHelper->getUser(),
        ]);
    }

    public function users()
    {
        $this->securityHelper->denyAccessUnlessAdmin();

        return $this->twig->render('pages/admin/pages/user_admin.html.twig', [
            'user' => $this->securityHelper->getUser(),
        ]);
    }

    public function addPost()
    {
        $this->securityHelper->denyAccessUnlessAdmin();
        if ('POST' == $this->serverRequest->getRequestMethod() && filter_input(INPUT_POST, 'csrfToken', FILTER_SANITIZE_SPECIAL_CHARS)) {
            try {
                list($errors, $message, $formData, $postSlug) = $this->postService->handleAddPostRequest();
            } catch (\RuntimeException $e) {
                $errors['featuredImage'] = $e->getMessage();
            }
            if (!empty($postSlug)) {
                $this->session->set('message', $message);
                $this->session->set('postSlug', $postSlug);
                $this->session->set('formData', $formData);
                $url = $this->request->generateUrl('admin_posts');
                $this->request->redirect($url);
            }
        }
        $csrfToken = $this->csrfTokenService->generateToken('addPost');

        return $this->twig->render('pages/admin/pages/add_post.html.twig', [
            'user' => $this->securityHelper->getUser(),
            'categories' => $this->categoryManager->findAll(),
            'tags' => $this->tagManager->findAll(),
            'csrfToken' => $csrfToken,
            'errors' => $errors ?? [],
            'message' => $message ?? '',
            'formData' => $this->session->get('formData', '') ?? $formData,
        ]);
    }

    public function editPost(int $postId)
    {
        $this->securityHelper->denyAccessUnlessAdmin();
        $post = $this->postManager->find($postId);
        if (!$post) {
            throw new \Exception('Artice non trouvé', 404);
        }
        if ('POST' == $this->serverRequest->getRequestMethod() && filter_input(INPUT_POST, 'csrfToken', FILTER_SANITIZE_SPECIAL_CHARS)) {
            list($errors, $message, $post, $postSlug, $formData) = $this->postService->handleEditPostRequest($post);
            if ($errors) {
                $this->session->set('formData', $formData);
            }
            if (!empty($postSlug)) {
                $this->session->set('message', $message);
                $this->session->set('postSlug', $postSlug);
                $this->session->set('formData', $formData);
                $url = $this->request->generateUrl('admin_posts');
                $this->request->redirect($url);
            }
        }
        $csrfToken = $this->csrfTokenService->generateToken('editPost');

        return $this->twig->render('pages/admin/pages/edit_post.html.twig', [
            'user' => $this->securityHelper->getUser(),
            'categories' => $this->categoryManager->findAll(),
            'tags' => $this->tagManager->findAll(),
            'post' => $post,
            'csrfToken' => $csrfToken,
            'errors' => $errors ?? [],
            'message' => $message ?? '',
            'formData' => $formData ?? '',
        ]);
    }
}
