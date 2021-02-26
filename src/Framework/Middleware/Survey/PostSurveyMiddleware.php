<?php

namespace Survey54\Reap\Framework\Middleware\Survey;

use Slim\Container;
use Survey54\Library\Middleware\ShieldMiddleware;
use Survey54\Library\Shield\AccessPoint\PrivateAccess;
use Survey54\Library\Shield\Checkpoint\Checkpoint;

class PostSurveyMiddleware extends ShieldMiddleware
{
    public function __construct(Container $container)
    {
        parent::__construct($container);
        // core service used, as call is made from core service, and easy to test with admin token from postman
        $this->allow((new PrivateAccess())
            ->addCheckpoint((new Checkpoint('core', 'surveys'))->create()));
    }
}
