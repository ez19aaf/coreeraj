<?php

namespace Survey54\Reap\Framework\Middleware\Respondent;

use Slim\Container;
use Slim\Http\Request;
use Survey54\Library\Middleware\ShieldMiddleware;
use Survey54\Library\Shield\AccessPoint\PrivateAccess;
use Survey54\Library\Shield\Checkpoint\Before;
use Survey54\Library\Shield\Checkpoint\Checkpoint;
use Survey54\Library\Shield\Checkpoint\RequirementData;
use Survey54\Library\Shield\Checkpoint\Task\Match;

class PutRespondentMiddleware extends ShieldMiddleware
{
    public function __construct(Container $container)
    {
        parent::__construct($container);

        $this->allow((new PrivateAccess())
            ->addCheckpoint((new Checkpoint('reap', 'respondents'))->update()
                ->strict(true)
                /**
                 * Requirement: id
                 * Use case: Respondent is only allowed to update their own details
                 */
                ->willExpect((new Before('id'))
                    ->satisfiedBy(new Match(function (RequirementData $data) {
                        /** @var $request Request */
                        $request = $data->getDataSource();

                        // get id from URL
                        $route     = $request->getAttribute('route');
                        $arguments = $route->getArguments();

                        return $arguments['uuid'] ?? null;
                    })))));
    }
}
