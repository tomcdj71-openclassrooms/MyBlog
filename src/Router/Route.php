<?php

declare(strict_types=1);

namespace App\Router;

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
            'home' => ['', HomeController::class, 'index', 'GET'],
            'blog' => ['/blog', BlogController::class, 'blogIndex', 'GET'],
            'not_found' => ['404', ErrorController::class, 'not_found', 'GET'],
            'blog_post' => ['/blog/post/{slug}', BlogController::class, 'blogPost', 'GET'],
            'blog_category' => ['/blog/category/{slug}', BlogController::class, 'blogCategory', 'GET'],
            'blog_tag' => ['/blog/tag/{slug}', BlogController::class, 'blogTag', 'GET'],
            'blog_author' => ['/blog/author/{slug}', BlogController::class, 'blogAuthor', 'GET'],
            'blog_date' => ['/blog/date/{date}', BlogController::class, 'blogDate', 'GET'],
            'my_profile' => ['/profile', UserController::class, 'profile', 'GET'],
            'user_profile' => ['/profile/{slug}', UserController::class, 'userProfile', 'GET'],
            'login' => ['/login', UserController::class, 'login', 'GET'],
            'register' => ['/register', UserController::class, 'register', 'GET'],
        ];
    }
}
