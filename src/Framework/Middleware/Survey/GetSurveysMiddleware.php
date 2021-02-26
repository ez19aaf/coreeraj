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
use Survey54\Reap\Application\Repository\RespondentRepository;
use Survey54\Reap\Domain\Respondent;

class GetSurveysMiddleware extends ShieldMiddleware
{
    public function __construct(Container $container)
    {
        parent::__construct($container);

        $this->allow((new PrivateAccess())
            ->addCheckpoint((new Checkpoint('reap', 'surveys'))->read()
                ->strict(true)
                /**
                 * Requirement: userId
                 * Use case: Respondent only can view surveys from reap service
                 */
                ->willExpect((new Before('userId'))
                    ->satisfiedBy(new Match(function (RequirementData $data) use ($container) {
                        /** @var RespondentRepository $respondentRepository */
                        $respondentRepository = $container[RespondentRepository::class];
                        /** @var Respondent $respondent */
                        $respondent = $respondentRepository->find($data->getValue()[0]);

                        /** @var $request Request */
                        $request     = $data->getDataSource();
                        $queryParams = array_merge($request->getQueryParams(), [
                            'respondentId' => $respondent->uuid,
                        ]);
                        $newRequest  = $request->withQueryParams($queryParams);
                        $data->updateSource($newRequest);

                        return $respondent->uuid; // respondent must be in DB
                    })))));
    }
}
