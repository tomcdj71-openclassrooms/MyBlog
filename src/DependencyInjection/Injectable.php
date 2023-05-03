<?php

declare(strict_types=1);

namespace App\DependencyInjection;

use App\Config\Configuration;
use App\Config\DatabaseConnexion;

class Injectable
{
    public static function register(Container $container): void
    {
        // Register Configuration and DatabaseConnexion
        $container->set(Configuration::class, function () {
            $configurationArray = require dirname(__DIR__).'/Config/.env.php';

            return new Configuration($configurationArray);
        });

        $container->set(DatabaseConnexion::class, function (Container $container) {
            return new DatabaseConnexion($container->get(Configuration::class));
        });
        // Register other dependency groups
        HelperInjectable::register($container);
        ManagerInjectable::register($container);
        ValidatorInjectable::register($container);
        ServiceInjectable::register($container);
        ControllerInjectable::register($container);
    }
}
