<?php

declare(strict_types=1);

namespace App\DependencyInjection;

use App\Manager\UserManager;
use App\Router\Session;
use App\Service\CsrfTokenService;
use App\Validator\BaseValidator;
use App\Validator\CommentFormValidator;
use App\Validator\EditProfileFormValidator;
use App\Validator\LoginFormValidator;
use App\Validator\RegistrationFormValidator;

class ValidatorInjectable
{
    public const VALIDATOR = [
        'base' => BaseValidator::class,
        'commentForm' => CommentFormValidator::class,
        'editProfileForm' => EditProfileFormValidator::class,
        'loginForm' => LoginFormValidator::class,
        'registrationForm' => RegistrationFormValidator::class,
    ];

    public const VALIDATOR_DEPENDENCIES = [
        'base' => [UserManager::class, Session::class, CsrfTokenService::class],
        'commentForm' => [UserManager::class, Session::class, CsrfTokenService::class],
        'editProfileForm' => [UserManager::class, Session::class, CsrfTokenService::class],
        'loginForm' => [UserManager::class, Session::class, CsrfTokenService::class],
        'registrationForm' => [UserManager::class, Session::class, CsrfTokenService::class],
    ];

    public static function register(Container $container): void
    {
        // build validators
        foreach (self::VALIDATOR as $validatorKey => $validatorClass) {
            $container->set($validatorClass, function (Container $container) use ($validatorClass, $validatorKey) {
                $dependencies = [];
                foreach (self::VALIDATOR_DEPENDENCIES[$validatorKey] ?? [] as $dependencyClass) {
                    $dependencies[] = $container->get($dependencyClass);
                }

                return new $validatorClass(...$dependencies);
            });
        }
    }
}
