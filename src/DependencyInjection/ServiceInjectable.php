<?php

declare(strict_types=1);

namespace App\DependencyInjection;

use App\Config\Configuration;
use App\Helper\SecurityHelper;
use App\Helper\StringHelper;
use App\Manager\CategoryManager;
use App\Manager\CommentManager;
use App\Manager\PostManager;
use App\Manager\TagManager;
use App\Manager\UserManager;
use App\Router\ServerRequest;
use App\Router\Session;
use App\Service\CommentService;
use App\Service\CsrfTokenService;
use App\Service\MailerService;
use App\Service\PostService;
use App\Service\ProfileService;

class ServiceInjectable
{
    public const SERVICE = [
        'comment' => CommentService::class,
        'csrfToken' => CsrfTokenService::class,
        'mailer' => MailerService::class,
        'post' => PostService::class,
        'profile' => ProfileService::class,
    ];

    public const SERVICE_DEPENDENCIES = [
        'comment' => [CommentManager::class, Session::class, CsrfTokenService::class, ServerRequest::class, SecurityHelper::class, UserManager::class],
        'csrfToken' => [Session::class],
        'post' => [ServerRequest::class, SecurityHelper::class, PostManager::class, Session::class, CsrfTokenService::class, UserManager::class, StringHelper::class, CategoryManager::class, TagManager::class],
        'profile' => [UserManager::class, CsrfTokenService::class, Session::class, SecurityHelper::class, ServerRequest::class],
    ];

    public static function register(Container $container): void
    {
        // build services
        foreach (self::SERVICE as $serviceKey => $serviceClass) {
            $container->set($serviceClass, function (Container $container) use ($serviceClass, $serviceKey) {
                $dependencies = [];
                foreach (self::SERVICE_DEPENDENCIES[$serviceKey] ?? [] as $dependencyClass) {
                    $dependencies[] = $container->get($dependencyClass);
                }

                return new $serviceClass(...$dependencies);
            });
        }
        // Build the mailer service
        $container->set(\Symfony\Component\Mailer\MailerInterface::class, function (Container $container) {
            $transport = new \Symfony\Component\Mailer\Transport\Smtp\EsmtpTransport(
                $container->get(Configuration::class)->get('mailer.smtp_host'),
                $container->get(Configuration::class)->get('mailer.smtp_port'),
            );

            return new \Symfony\Component\Mailer\Mailer($transport);
        });
        $container->set(self::SERVICE['mailer'], function (Container $container) {
            $mailerInterface = $container->get(\Symfony\Component\Mailer\MailerInterface::class);
            $mode = $container->get(Configuration::class)->get('mode');

            return new MailerService($mailerInterface, $mode);
        });
    }
}
