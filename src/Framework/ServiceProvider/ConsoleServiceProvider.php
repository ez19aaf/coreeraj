<?php

namespace Survey54\Reap\Framework\ServiceProvider;

use Slim\Container;
use Survey54\Reap\Application\Repository\InsightRepository;
use Survey54\Reap\Application\Repository\RespondentRepository;
use Survey54\Reap\Application\Repository\RespondentSurveyRepository;
use Survey54\Reap\Application\Repository\ResponseRepository;
use Survey54\Reap\Application\Repository\SurveyRepository;
use Survey54\Reap\Framework\Console\GenerateAllQuestionFlows;
use Survey54\Reap\Framework\Console\GenerateAMResponses;
use Survey54\Reap\Framework\Console\GenerateColgatePilot;
use Survey54\Reap\Framework\Console\GenerateSampleResponses;

class ConsoleServiceProvider implements ServiceProviderInterface
{
    /**
     * @param Container $container
     */
    public function register(Container $container): void
    {
        $container[GenerateAMResponses::class] = fn() => new GenerateAMResponses(
            $container[SurveyRepository::class],
            $container[ResponseRepository::class],
            $container[RespondentRepository::class],
            $container[RespondentSurveyRepository::class],
        );

        $container[GenerateColgatePilot::class] = fn() => new GenerateColgatePilot(
            $container[SurveyRepository::class],
        );

        $container[GenerateSampleResponses::class] = fn() => new GenerateSampleResponses(
            $container[SurveyRepository::class],
            $container[InsightRepository::class],
            $container[ResponseRepository::class],
            $container[RespondentRepository::class],
            $container[RespondentSurveyRepository::class],
        );

        $container[GenerateAllQuestionFlows::class] = fn() => new GenerateAllQuestionFlows(
            $container[SurveyRepository::class],
        );
    }
}
