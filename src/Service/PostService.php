<?php

declare(strict_types=1);

namespace App\Service;

use App\Helper\ImageHelper;
use App\Helper\SecurityHelper;
use App\Helper\StringHelper;
use App\Manager\CategoryManager;
use App\Manager\PostManager;
use App\Manager\TagManager;
use App\Manager\UserManager;
use App\Router\ServerRequest;
use App\Router\Session;
use App\Validator\PostFormValidator;

class PostService extends AbstractService
{
    protected PostManager $postManager;
    protected Session $session;
    protected CsrfTokenService $csrfTokenService;
    protected ServerRequest $serverRequest;
    protected SecurityHelper $securityHelper;
    protected UserManager $userManager;
    protected StringHelper $stringHelper;
    protected ImageHelper $imageHelper;
    protected CategoryManager $categoryManager;
    protected TagManager $tagManager;

    public function __construct(
        ServerRequest $serverRequest,
        SecurityHelper $securityHelper,
        PostManager $postManager,
        Session $session,
        CsrfTokenService $csrfTokenService,
        UserManager $userManager,
        StringHelper $stringHelper,
        CategoryManager $categoryManager,
        TagManager $tagManager
    ) {
        $this->postManager = $postManager;
        $this->session = $session;
        $this->csrfTokenService = $csrfTokenService;
        $this->serverRequest = $serverRequest;
        $this->securityHelper = $securityHelper;
        $this->userManager = $userManager;
        $this->stringHelper = $stringHelper;
        $this->categoryManager = $categoryManager;
        $this->tagManager = $tagManager;
        $this->imageHelper = new ImageHelper('uploads/featured/', 1200, 900);
    }

    public function getUserPostsData()
    {
        try {
            $user = $this->securityHelper->getUser();
            if (!$user) {
                header('Location: /login');
            }
            $offset = $this->serverRequest->getQuery('offset') ? intval($this->serverRequest->getQuery('offset')) : 1;
            $limit = $this->serverRequest->getQuery('limit') ? intval($this->serverRequest->getQuery('limit')) : 10;
            $page = intval($offset / $limit) + 1;
            $userPostsData = $this->postManager->findUserPosts($user->getId(), $page, $limit);
            $userPosts = $userPostsData['posts'];
            $userPostsArray = [];
            $comments = '';
            foreach ($userPosts as $post) {
                $numberOfComments = isset($comments['number_of_comments']) ? $comments['number_of_comments'] : 0;
                $tags = array_map(function ($tag) {
                    return $tag->getName();
                }, $post->getTags());
                $userPostsArray[] = [
                    'id' => $post->getId(),
                    'title' => $post->getTitle(),
                    'slug' => $post->getSlug(),
                    'created_at' => $post->getCreatedAt(),
                    'is_enabled' => $post->getIsEnabled(),
                    'category' => $post->getCategory()->getName(),
                    'comments' => $numberOfComments.' commentaire(s)',
                    'tags' => $tags,
                    'type' => 'myPosts',
                ];
            }
            $totalPosts = $userPostsData['count'];

            return [
                'rows' => $userPostsArray,
                'total' => $totalPosts,
            ];
        } catch (\Exception $e) {
            throw new \RuntimeException('Une erreur est survenue lors de la récupération des articles.');
        }
    }

    public function getOtherUserPostsData(int $userId)
    {
        try {
            $offset = $this->serverRequest->getQuery('offset') ? intval($this->serverRequest->getQuery('offset')) : 1;
            $limit = $this->serverRequest->getQuery('limit') ? intval($this->serverRequest->getQuery('limit')) : 10;
            $page = intval($offset / $limit) + 1;
            $otherUserPostsData = $this->postManager->findUserPosts($userId, $page, $limit);
            $otherUserPosts = $otherUserPostsData['posts'];
            $otherUserPostsArray = [];
            $comments = '';
            foreach ($otherUserPosts as $post) {
                $numberOfComments = isset($comments['number_of_comments']) ? $comments['number_of_comments'] : 0;
                $tags = array_map(function ($tag) {
                    return $tag->getName();
                }, $post->getTags());
                $otherUserPostsArray[] = [
                    'id' => $post->getId(),
                    'title' => $post->getTitle(),
                    'slug' => $post->getSlug(),
                    'created_at' => $post->getCreatedAt(),
                    'is_enabled' => $post->getIsEnabled(),
                    'category' => $post->getCategory()->getName(),
                    'comments' => $numberOfComments.' commentaire(s)',
                    'tags' => $tags,
                    'type' => 'otherPosts',
                ];
            }
            $totalPosts = $otherUserPostsData['count'];

            return [
                'rows' => $otherUserPostsArray,
                'total' => $totalPosts,
            ];
        } catch (\Exception $e) {
            throw new \RuntimeException('Une erreur est survenue lors de la récupération des articles.');
        }
    }

    public function handleAddPostRequest()
    {
        $errors = [];
        $csrfToCheck = $this->serverRequest->getPost('csrfToken');
        if (!$this->csrfTokenService->checkCsrfToken('addPost', $csrfToCheck)) {
            $errors[] = 'Jeton CSRF invalide.';
        }
        $formData = $this->getFormData();
        if (empty($formData['featuredImage']['name'])) {
            $this->session->set('errors', ['Veuillez sélectionner une image.']);
            $this->session->set('formData', $formData);

            throw new \RuntimeException('Veuillez sélectionner une image.');
        }
        $postFormValidator = new PostFormValidator($this->userManager, $this->session, $this->csrfTokenService);
        $response = $postFormValidator->validate($formData);
        $postSlug = $response['valid'] ? $this->createPost($formData) : null;
        $message = $postSlug ? 'Votre article a été ajouté avec succès!' : null;
        $errors = $response['valid'] ? null : $response['errors'];

        return [$errors, $message, $formData, $postSlug];
    }

    public function getFormData()
    {
        $fields = ['title', 'chapo', 'content', 'category', 'tags'];
        $formData = array_map(function ($field) {
            return $this->serverRequest->getPost($field, '');
        }, array_combine($fields, $fields));
        $formData['tags'] = implode(',', $formData['tags']);
        $formData['featuredImage'] = $_FILES['featuredImage'] ?? null;
        $formData['csrfToken'] = $this->serverRequest->getPost('csrfToken');

        return $formData;
    }

    public function handleEditPostRequest($post)
    {
        $errors = [];
        $message = [];
        $postSlug = null;
        $tagsUpdated = null;
        $formData = $this->getFormData();
        $csrfToCheck = $this->serverRequest->getPost('csrfToken');
        if (!$this->csrfTokenService->checkCsrfToken('editPost', $csrfToCheck)) {
            $errors[] = 'Jeton CSRF invalide.';
        }
        $formData['slug'] = isset($formData['title']) ? $this->stringHelper->slugify($formData['title']) : $post->getSlug();
        $formData['updatedAt'] = (new \DateTime())->format('Y-m-d H:i:s');
        $formData['isEnabled'] = false;
        foreach ($formData as $key => $value) {
            if ('csrfToken' === $key) {
                continue;
            }
            $postGetter = 'get'.ucfirst($key);
            if (method_exists($post, $postGetter) && $post->{$postGetter}() !== $value) {
                $message[] = ucfirst($key).' a été modifié.';
            }
        }
        if (empty($errors)) {
            $postFormValidator = new PostFormValidator($this->userManager, $this->session, $this->csrfTokenService);
            $response = $postFormValidator->validate($formData);
            if (empty($formData['featuredImage']['name'])) {
                $formData['featuredImage'] = $post->getFeaturedImage();
            }
            $responseData = $response['valid'] ? $this->editPost($post, $formData) : null;
            $postSlug = $responseData['postSlug'] ?? null;
            $tagsUpdated = $responseData['tagsUpdated'] ?? null;
            $message = $response['valid'] && $postSlug ? 'Votre article a été modifié avec succès!' : null;
            $errors = $response['valid'] ? $errors : $response['errors'];
        }

        return [$errors, $message, $post, $postSlug, $formData, $tagsUpdated];
    }

    public function editPost($post, $data)
    {
        try {
            $fields = ['title', 'chapo', 'content', 'category', 'tags', 'featuredImage', 'slug', 'updated_at', 'is_enabled'];
            foreach ($fields as $field) {
                $setter = 'set'.$field;
                $dataKey = lcfirst($field);
                if (isset($data[$dataKey])) {
                    switch ($field) {
                        case 'category':
                            $this->setCategory($post, $data[$dataKey]);

                            break;

                        case 'tags':
                            $this->setTags($post, $data[$dataKey]);

                            break;

                        case 'featuredImage':
                            if (isset($data[$dataKey])) {
                                $featuredImage = $this->setFeaturedImage($post, $data[$dataKey]);
                                if (null !== $featuredImage) {
                                    $post->{$setter}($featuredImage);
                                    $data[$dataKey] = $featuredImage;
                                }
                            }

                            break;

                        default:
                            $post->{$setter}($data[$dataKey]);

                            break;
                    }
                }
            }
            $data['isEnabled'] = $post->getIsEnabled();
            $postUpdated = $this->postManager->updatePost($post, $data);
            $tagsUpdated = $this->postManager->updatePostTags($post, $post->getTags());
            if ($postUpdated && $tagsUpdated) {
                return [
                    'formData' => $data,
                    'postSlug' => $post->getSlug() ?? null,
                    'tagsUpdated' => $tagsUpdated ?? null,
                ];
            }
        } catch (\Exception $e) {
            error_log($e->getMessage());
        }
    }

    public function createPost(array $data)
    {
        try {
            $user = $this->securityHelper->getUser();
            $createdAt = new \DateTime();
            $createdAt = $createdAt->format('Y-m-d H:i:s');
            $filename = $this->imageHelper->uploadImage($data['featuredImage'], 1200, 900);
            if (0 === strpos($filename, 'Error')) {
                throw new \RuntimeException($filename);
            }
            $filename = explode('.', $filename)[0];
            $formData = [
                'title' => $data['title'],
                'content' => $data['content'],
                'author' => $user->getId(),
                'chapo' => $data['chapo'],
                'createdAt' => $createdAt,
                'updatedAt' => $createdAt,
                'isEnabled' => 1,
                'featuredImage' => $filename,
                'category' => $data['category'],
                'slug' => $this->stringHelper->slugify($data['title']),
                'tags' => $data['tags'],
                'csrfToken' => $data['csrfToken'],
            ];
            $createdPost = $this->postManager->create($formData);

            return $createdPost ? $createdPost->getSlug() : null;
        } catch (\Exception $e) {
            error_log($e->getMessage());
        }
    }

    private function setCategory($post, $categoryData)
    {
        $category = $this->categoryManager->find((int) $categoryData);
        $post->setCategory($category);
    }

    private function setTags($post, $tagsData)
    {
        $tags = $this->tagManager->findByIds(explode(',', $tagsData));
        $post->addTags($tags);
    }

    private function setFeaturedImage($post, $imageData)
    {
        if (!empty($imageData['name']) && UPLOAD_ERR_NO_FILE !== $imageData['error']) {
            $filename = $this->imageHelper->uploadImage($imageData, 1200, 900);
            if (0 === strpos($filename, 'Erreur')) {
                throw new \RuntimeException($filename);
            }
            $filename = explode('.', $filename)[0];
            $imageData = $filename;
            $post->setFeaturedImage($imageData);

            return $imageData;
        }
        $imageData = $post->getFeaturedImage();
    }
}
