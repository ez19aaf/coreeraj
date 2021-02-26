<?php

namespace Survey54\Reap\Framework\Middleware\Survey;

use Slim\Container;
use Slim\Http\Request;
use Survey54\Library\Middleware\ShieldMiddleware;
use Survey54\Library\Shield\AccessPoint\PrivateAccess;
use Survey54\Library\Shield\Checkpoint\Before;
use Survey54\Library\Shield\Checkpoint\Checkpoint;
use Survey54\Library\Shield\Checkpoint\RequirementData;
use Survey54\Library\Shield\Checkpoint\Task\Match;
use Survey54\Reap\Application\SurveyService;

class PostSurveyUserAllowedMiddleware extends ShieldMiddleware
{
    public function __construct(Container $container)
    {
        parent::__construct($container);

        $this->allow((new PrivateAccess())
            ->addCheckpoint((new Checkpoint('core', 'surveys'))->create()
                ->strict(true)
                /**
                 * Requirement: userId
                 * Use case: User is only allowed to create their own surveys
                 */
                ->willExpect((new Before('userId'))
                    ->satisfiedBy(new Match(function (RequirementData $data) use ($container) {
                        /** @var $request Request */
                        $request = $data->getDataSource();

                        // get id from URL
                        $route     = $request->getAttribute('route');
                        $arguments = $route->getArguments();
                        if (!$surveyId = $arguments['uuid'] ?? null) {
                            return null;
                        }

                        /** @var SurveyService $surveyService */
                        $surveyService = $container[SurveyService::class];
                        if (!$survey = $surveyService->find($surveyId)) {
                            return null;
                        }

                        return $survey->userId;
                    })))));
    }
}
