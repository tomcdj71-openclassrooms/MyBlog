<?php

declare(strict_types=1);

namespace App\Router;

use App\Controller\AdminController;
use App\Controller\AjaxController;
use App\Controller\BlogController;
use App\Controller\ErrorController;
use App\Controller\HomeController;
use App\Controller\UserController;

class Route
{
    /**
     * Define the routes.
     *
     * @var array
     */
    public function getRoutes()
    {
        return [
            'home' => ['/', HomeController::class, 'index', 'GET|POST', 'Portfolio'],
            'error_page' => ['error', ErrorController::class, 'notFound', 'GET', 'Erreur'],
            'user_profile' => ['/profile/{slug}', UserController::class, 'userProfile', 'GET', 'Utilisateur'],
            'blog' => ['/blog', BlogController::class, 'blogIndex', 'GET', 'Blog'],
            'blog_post' => ['/blog/post/{slug}', BlogController::class, 'blogPost', 'GET|POST', 'Article'],
            'blog_category' => ['/blog/category/{slug}', BlogController::class, 'blogCategory', 'GET', 'Catégories'],
            'blog_tag' => ['/blog/tag/{slug}', BlogController::class, 'blogTag', 'GET', 'Tags'],
            'blog_author' => ['/blog/author/{username}', BlogController::class, 'blogAuthor', 'GET', 'Auteur'],
            'blog_date' => ['/blog/date/{date}', BlogController::class, 'blogDate', 'GET', 'Date'],
            'my_profile' => ['/profile', UserController::class, 'profile', 'GET|POST', 'Mon Profil'],
            'login' => ['/login', UserController::class, 'login', 'GET|POST', 'Se Connecter'],
            'logout' => ['/logout', UserController::class, 'logout', 'GET', 'Déconnexion'],
            'register' => ['/register', UserController::class, 'register', 'GET|POST', "S'inscrire"],
            'admin_index' => ['/admin', AdminController::class, 'index', 'GET', 'Admin Dashboard'],
            'admin_tags' => ['/admin/tags', AdminController::class, 'tags', 'GET|POST', 'Gestion des Tags'],
            'admin_categories' => ['/admin/categories', AdminController::class, 'categories', 'GET|POST', 'Gestion des Catégories'],
            'admin_posts' => ['/admin/posts', AdminController::class, 'posts', 'GET|POST', 'Gestion des Articles'],
            'admin_post_delete' => ['/admin/post/{id}/delete', AdminController::class, 'post', 'POST', 'Supprimer un Article'],
            'admin_post_edit' => ['/admin/post/{id}/edit', AdminController::class, 'editPost', 'GET|POST', 'Modifier un Article'],
            'admin_past_add' => ['/admin/post/add', AdminController::class, 'addPost', 'GET|POST', 'Ajouter un Article'],
            'admin_users' => ['/admin/users', AdminController::class, 'users', 'GET|POST', 'Gestion des Utilisateurs'],
            'admin_comments' => ['/admin/comments', AdminController::class, 'comments', 'GET|POST', 'Gestion des Commentaires'],
            'admin_comment_delete' => ['/admin/comment/{id}/delete', AdminController::class, 'comment', 'POST', 'Supprimer un Commentaire'],
            'edit_comment' => ['/comment/{id}/edit', BlogController::class, 'editComment', 'GET|POST', 'Modifier un Commentaire'],
            'ajax_user_comments' => ['/ajax/user-comments', AjaxController::class, 'myComments', 'GET', 'Mes Commentaires'],
            'ajax_user_posts' => ['/ajax/user-posts', AjaxController::class, 'myPosts', 'GET', 'Mes Articles'],
            'ajax_admin_all_comments' => ['/ajax/admin-all-comments', AjaxController::class, 'manageAllComments', 'GET', 'Gestion des Commentaires'],
            'ajax_admin_all_posts' => ['/ajax/admin-all-posts', AjaxController::class, 'allPosts', 'GET', 'Gestion des Articles'],
            'ajax_admin_all_users' => ['/ajax/admin-all-users', AjaxController::class, 'allUsers', 'GET', 'Gestion des Utilisateurs'],
            'ajax_admin_all_tags' => ['/ajax/admin-all-tags', AjaxController::class, 'allTags', 'GET', 'Gestion des Tags'],
            'ajax_admin_all_categories' => ['/ajax/admin-all-categories', AjaxController::class, 'allCategories', 'GET', 'Gestion des Catégories'],
            'ajax_admin_toggle_comment' => ['/ajax/admin-toggle-comment/{id}', AjaxController::class, 'toggleCommentStatus', 'POST', 'Activer/Désactiver un Commentaire'],
            'ajax_admin_toggle_post' => ['/ajax/admin-toggle-post/{id}', AjaxController::class, 'togglePostStatus', 'POST', 'Activer/Désactiver un Article'],
        ];
    }
}
