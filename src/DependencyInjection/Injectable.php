<?php

declare(strict_types=1);

namespace App\DependencyInjection;

use App\Config\Configuration;
use App\Helper\SecurityHelper;
use App\Helper\StringHelper;
use App\Helper\TwigHelper;
use App\Manager\CategoryManager;
use App\Manager\CommentManager;
use App\Manager\PostManager;
use App\Manager\TagManager;
use App\Manager\UserManager;
use App\Middleware\AuthenticationMiddleware;
use App\Router\Request;
use App\Router\ServerRequest;
use App\Router\Session;
use App\Service\CommentService;
use App\Service\MailerService;
use App\Service\PostService;
use App\Service\ProfileService;

class Injectable
{
    public static function register(Container $container): void
    {
        $container->set(Configuration::class, function () {
            $configurationArray = require dirname(__DIR__).'/Config/.env.php';

            return new Configuration($configurationArray);
        });
        $container->set(Request::class, function (Container $container) {
            return new Request($container);
        });
        $container->set(ServerRequest::class, new ServerRequest($_SERVER));
        $container->set(Session::class, new Session());
        $container->set(SecurityHelper::class, new SecurityHelper($container->get(Session::class)));
        $container->set(AuthenticationMiddleware::class);
        $container->set(CategoryManager::class);
        $container->set(TagManager::class);
        $container->set(PostManager::class);
        $container->set(UserManager::class);
        $container->set(CommentManager::class);
        $container->set(StringHelper::class);
        $container->set(TwigHelper::class);
        $container->set(ProfileService::class);
        $container->set(CommentService::class);
        $container->set(PostService::class);
        $container->set(\Symfony\Component\Mailer\MailerInterface::class, function (Container $container) {
            $transport = new \Symfony\Component\Mailer\Transport\Smtp\EsmtpTransport(
                $container->get(Configuration::class)->get('mailer.smtp_host'),
                $container->get(Configuration::class)->get('mailer.smtp_port')
            );

            return new \Symfony\Component\Mailer\Mailer($transport);
        });
        $container->set(MailerService::class, function (Container $container) {
            $mailerInterface = $container->get(\Symfony\Component\Mailer\MailerInterface::class);

            return new MailerService($mailerInterface);
        });
    }
}
