<?php

declare(strict_types=1);

namespace App\Service;

use App\DependencyInjection\Container;
use App\Helper\SecurityHelper;
use App\Manager\PostManager;
use App\Router\ServerRequest;

class AbstractService
{
    protected Container $container;
    protected SecurityHelper $securityHelper;
    protected ServerRequest $serverRequest;
    protected PostManager $postManager;

    public function __construct(Container $container)
    {
        $this->container = $container;
        $this->container->injectProperties($this);
        $this->securityHelper = $container->get(SecurityHelper::class);
        $this->serverRequest = $container->get(ServerRequest::class);
        $this->postManager = $container->get(PostManager::class);
    }
}
