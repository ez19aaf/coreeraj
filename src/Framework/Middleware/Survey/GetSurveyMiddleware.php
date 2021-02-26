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
use Survey54\Reap\Application\Repository\SurveyRepository;

class GetSurveyMiddleware extends ShieldMiddleware
{
    public function __construct(Container $container)
    {
        parent::__construct($container);

        $this->allow((new PrivateAccess())
            ->addCheckpoint((new Checkpoint('reap', 'surveys'))->read()
                ->strict(true)
                /**
                 * Requirement: userId
                 * Use case: User is only allowed to view their own survey status
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

                        if ($surveyId === SAMPLE_SURVEY_ID) {
                            // check that this user is an org not just any user. Could we have TYPE in the token? easier.
                            return $data->getValue()[0] ?? null; // The expected user's UUID
                        }

                        /** @var SurveyRepository $surveyRepository */
                        $surveyRepository = $container[SurveyRepository::class];
                        $survey           = $surveyRepository->find($surveyId);

                        return $survey->userId ?? null;
                    })))));
    }
}
