<?php

namespace Survey54\Reap\Framework\Middleware\Insight;

use Slim\Container;
use Survey54\Library\Middleware\ShieldMiddleware;
use Survey54\Library\Shield\AccessPoint\PrivateAccess;
use Survey54\Library\Shield\Checkpoint\Checkpoint;

class PostInsightsMiddleware extends ShieldMiddleware
{
    public function __construct(Container $container)
    {
        parent::__construct($container);

        $this->allow((new PrivateAccess())
            ->addCheckpoint((new Checkpoint('reap', 'insights'))->create()));
    }
}
