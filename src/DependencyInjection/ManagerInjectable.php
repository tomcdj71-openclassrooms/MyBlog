<?php

declare(strict_types=1);

namespace App\DependencyInjection;

use App\Config\DatabaseConnexion;
use App\Manager\CategoryManager;
use App\Manager\CommentManager;
use App\Manager\PostManager;
use App\Manager\TagManager;
use App\Manager\UserManager;

class ManagerInjectable
{
    public const MANAGER = [
        'category' => CategoryManager::class,
        'comment' => CommentManager::class,
        'post' => PostManager::class,
        'tag' => TagManager::class,
        'user' => UserManager::class,
    ];

    public const MANAGER_DEPENDENCIES = [
        'category' => [DatabaseConnexion::class],
        'comment' => [DatabaseConnexion::class],
        'post' => [DatabaseConnexion::class],
        'tag' => [DatabaseConnexion::class],
        'user' => [DatabaseConnexion::class],
    ];

    public static function register(Container $container): void
    {
        // build managers
        foreach (self::MANAGER as $managerKey => $managerClass) {
            $container->set($managerClass, function (Container $container) use ($managerClass, $managerKey) {
                $dependencies = [];
                foreach (self::MANAGER_DEPENDENCIES[$managerKey] ?? [] as $dependencyClass) {
                    $dependencies[] = $container->get($dependencyClass);
                }

                return new $managerClass(...$dependencies);
            });
        }
    }
}
