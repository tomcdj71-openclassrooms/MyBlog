<?php

declare(strict_types=1);

namespace App\DependencyInjection;

use App\Config\Configuration;
use App\Controller\AdminController;
use App\Controller\AjaxController;
use App\Controller\BlogController;
use App\Controller\ErrorController;
use App\Controller\HomeController;
use App\Controller\UserController;
use App\Helper\SecurityHelper;
use App\Helper\TwigHelper;
use App\Manager\CategoryManager;
use App\Manager\CommentManager;
use App\Manager\PostManager;
use App\Manager\TagManager;
use App\Manager\UserManager;
use App\Router\Request;
use App\Router\ServerRequest;
use App\Router\Session;
use App\Service\CommentService;
use App\Service\CsrfTokenService;
use App\Service\MailerService;
use App\Service\PostService;

class ControllerInjectable
{
    public const CONTROLLER = [
        'admin' => AdminController::class,
        'ajax' => AjaxController::class,
        'blog' => BlogController::class,
        'error' => ErrorController::class,
        'home' => HomeController::class,
        'user' => UserController::class,
    ];

    public const CONTROLLER_DEPENDENCIES = [
        'admin' => [TwigHelper::class, Session::class, ServerRequest::class, SecurityHelper::class, UserManager::class, Request::class, CategoryManager::class, TagManager::class, PostService::class, CsrfTokenService::class, PostManager::class],
        'ajax' => [TwigHelper::class, Session::class, ServerRequest::class, SecurityHelper::class, UserManager::class, Request::class, Configuration::class, MailerService::class],
        'blog' => [TwigHelper::class, Session::class, ServerRequest::class, SecurityHelper::class, UserManager::class, Request::class, CategoryManager::class, TagManager::class, CommentManager::class, CommentService::class, PostManager::class, CsrfTokenService::class],
        'error' => [TwigHelper::class, Session::class, ServerRequest::class, SecurityHelper::class, UserManager::class, Request::class],
        'home' => [MailerService::class, Configuration::class],
        'user' => [TwigHelper::class, Session::class, ServerRequest::class, SecurityHelper::class, UserManager::class, Request::class, CsrfTokenService::class],
    ];

    public static function register(Container $container): void
    {
        // build the controllers
        foreach (self::CONTROLLER as $controllerKey => $controllerClass) {
            $container->set($controllerClass, function (Container $container) use ($controllerClass, $controllerKey) {
                $dependencies = [];
                foreach (self::CONTROLLER_DEPENDENCIES[$controllerKey] ?? [] as $dependencyClass) {
                    $dependencies[] = $container->get($dependencyClass);
                }

                return new $controllerClass(...$dependencies);
            });
        }
    }
}
