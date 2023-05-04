<?php

declare(strict_types=1);

namespace App\Service;

use App\Helper\SecurityHelper;
use App\Manager\PostManager;
use App\Router\ServerRequest;

class AbstractService
{
    protected SecurityHelper $securityHelper;
    protected ServerRequest $serverRequest;
    protected PostManager $postManager;

    public function __construct(SecurityHelper $securityHelper, ServerRequest $serverRequest, PostManager $postManager)
    {
        $this->securityHelper = $securityHelper;
        $this->serverRequest = $serverRequest;
        $this->postManager = $postManager;
    }
}
