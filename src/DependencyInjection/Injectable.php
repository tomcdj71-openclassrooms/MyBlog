<?php

declare(strict_types=1);

namespace App\DependencyInjection;

use App\Config\Configuration;
use App\Config\DatabaseConnexion;
use App\Helper\SecurityHelper;
use App\Helper\StringHelper;
use App\Helper\TwigHelper;
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
use App\Validator\BaseValidator;
use App\Validator\CommentFormValidator;
use App\Validator\LoginFormValidator;
use App\Validator\RegistrationFormValidator;

class Injectable
{
    public static function register(Container $container): void
    {
        $container->set(Configuration::class, function () {
            $configurationArray = require dirname(__DIR__).'/Config/.env.php';

            return new Configuration($configurationArray);
        });
        $container->set(DatabaseConnexion::class, function (Container $container) {
            return new DatabaseConnexion($container->get(Configuration::class));
        });
        $container->set(UserManager::class, function (Container $container) {
            return new UserManager($container->get(DatabaseConnexion::class));
        });
        $container->set(Session::class, new Session());
        $container->set(CsrfTokenService::class, function (Container $container) {
            return new CsrfTokenService(
                $container->get(Session::class)
            );
        });
        $container->set(BaseValidator::class, function (Container $container) {
            return new BaseValidator($container->get(UserManager::class), $container->get(Session::class), $container->get(CsrfTokenService::class));
        });
        $container->set(SecurityHelper::class, function (Container $container) {
            return new SecurityHelper(
                $container->get(UserManager::class),
                $container->get(Session::class)
            );
        });
        $container->set(RegistrationFormValidator::class, function (Container $container) {
            return new RegistrationFormValidator(
                $container->get(UserManager::class),
                $container->get(Session::class),
                $container->get(CsrfTokenService::class)
            );
        });
        $container->set(LoginFormValidator::class, function (Container $container) {
            return new LoginFormValidator(
                $container->get(UserManager::class),
                $container->get(Session::class),
                $container->get(CsrfTokenService::class)
            );
        });

        $container->set(CommentFormValidator::class, function (Container $container) {
            return new CommentFormValidator(
                $container->get(UserManager::class),
                $container->get(Session::class),
                $container->get(CsrfTokenService::class)
            );
        });

        $container->set(ServerRequest::class, new ServerRequest($_SERVER));
        $container->set(CategoryManager::class);
        $container->set(TagManager::class);
        $container->set(PostManager::class);
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
