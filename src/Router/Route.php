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
            'home' => ['/', HomeController::class, 'index', 'GET|POST'],
            'not_found' => ['404', ErrorController::class, 'not_found', 'GET'],
            'blog' => ['/blog', BlogController::class, 'blogIndex', 'GET'],
            'blog_post' => ['/blog/post/{slug}', BlogController::class, 'blogPost', 'GET|POST'],
            'blog_category' => ['/blog/category/{slug}', BlogController::class, 'blogCategory', 'GET'],
            'blog_tag' => ['/blog/tag/{slug}', BlogController::class, 'blogTag', 'GET'],
            'blog_author' => ['/blog/author/{username}', BlogController::class, 'blogAuthor', 'GET'],
            'blog_date' => ['/blog/date/{date}', BlogController::class, 'blogDate', 'GET'],
            'my_profile' => ['/profile', UserController::class, 'profile', 'GET|POST'],
            'user_profile' => ['/profile/{slug}', UserController::class, 'userProfile', 'GET'],
            'login' => ['/login', UserController::class, 'login', 'GET|POST'],
            'logout' => ['/logout', UserController::class, 'logout', 'GET'],
            'register' => ['/register', UserController::class, 'register', 'GET|POST'],
            'admin_index' => ['/admin', AdminController::class, 'index', 'GET'],
            'admin_tags' => ['/admin/tags', AdminController::class, 'tags', 'GET|POST'],
            'admin_tag_delete' => ['/admin/tag/{id}/delete', AdminController::class, 'tag', 'POST'],
            'admin_categories' => ['/admin/categories', AdminController::class, 'categories', 'GET|POST'],
            'admin_category_delete' => ['/admin/category/{id}/delete', AdminController::class, 'category', 'POST'],
            'admin_posts' => ['/admin/posts', AdminController::class, 'posts', 'GET|POST'],
            'admin_post_delete' => ['/admin/post/{id}/delete', AdminController::class, 'post', 'POST'],
            'admin_users' => ['/admin/users', AdminController::class, 'users', 'GET|POST'],
            'admin_user_delete' => ['/admin/user/{id}/delete', AdminController::class, 'user', 'POST'],
            'admin_comments' => ['/admin/comments', AdminController::class, 'comments', 'GET|POST'],
            'admin_comment_delete' => ['/admin/comment/{id}/delete', AdminController::class, 'comment', 'POST'],
            'edit_comment' => ['/comment/{id}/edit', BlogController::class, 'editComment', 'GET|POST'],
            'ajax_user_comments' => ['/ajax/user-comments', AjaxController::class, 'myComments', 'GET'],
            'ajax_user_posts' => ['/ajax/user-posts', AjaxController::class, 'myPosts', 'GET'],
            'ajax_admin_all_comments' => ['/ajax/admin-all-comments', AjaxController::class, 'manageAllComments', 'GET'],
            'ajax_admin_all_posts' => ['/ajax/admin-all-posts', AjaxController::class, 'allPosts', 'GET'],
            'ajax_admin_all_users' => ['/ajax/admin-all-users', AjaxController::class, 'allUsers', 'GET'],
            'ajax_admin_all_tags' => ['/ajax/admin-all-tags', AjaxController::class, 'allTags', 'GET'],
            'ajax_admin_all_categories' => ['/ajax/admin-all-categories', AjaxController::class, 'allCategories', 'GET'],
            'ajax_admin_toggle_comment' => ['/ajax/admin-toggle-comment/{id}', AjaxController::class, 'toggleCommentStatus', 'POST'],
        ];
    }
}
