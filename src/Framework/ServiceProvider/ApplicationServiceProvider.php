<?php

namespace Survey54\Reap\Framework\ServiceProvider;

use Slim\Container;
use Survey54\Library\Adapter\CloudinaryAdapter;
use Survey54\Library\Adapter\IpToCountryAdapter;
use Survey54\Library\Adapter\SwiftMailerAdapter;
use Survey54\Library\Adapter\TextLocalAdapter;
use Survey54\Library\Message\MessageService;
use Survey54\Library\Message\TextMessageService;
use Survey54\Reap\Application\AppReviewService;
use Survey54\Reap\Application\FileService;
use Survey54\Reap\Application\GdprService;
use Survey54\Reap\Application\GhostService;
use Survey54\Reap\Application\GroupService;
use Survey54\Reap\Application\InsightService;
use Survey54\Reap\Application\LogService;
use Survey54\Reap\Application\OpenService;
use Survey54\Reap\Application\Repository\AirtimeCsvRepository;
use Survey54\Reap\Application\Repository\AppReviewRepository;
use Survey54\Reap\Application\Repository\GdprRepository;
use Survey54\Reap\Application\Repository\GhostRepository;
use Survey54\Reap\Application\Repository\GroupRepository;
use Survey54\Reap\Application\Repository\InsightRepository;
use Survey54\Reap\Application\Repository\LogRepository;
use Survey54\Reap\Application\Repository\RespondentRepository;
use Survey54\Reap\Application\Repository\RespondentSurveyRepository;
use Survey54\Reap\Application\Repository\ResponseRepository;
use Survey54\Reap\Application\Repository\SurveyRepository;
use Survey54\Reap\Application\RespondentService;
use Survey54\Reap\Application\ResponseService;
use Survey54\Reap\Application\SurveyService;
use Survey54\Reap\Framework\Adapter\AfricaTalkingAdapter;
use Survey54\Reap\Framework\Adapter\AirtimeAdapter;

class ApplicationServiceProvider implements ServiceProviderInterface
{
    /**
     * @param Container $container
     */
    public function register(Container $container): void
    {
        $container[AppReviewService::class] = fn() => new AppReviewService(
            $container[AppReviewRepository::class],
            $container[RespondentRepository::class],
        );

        $container[FileService::class] = fn() => new FileService(
            $container[SurveyRepository::class],
            $container[ResponseService::class],
        );

        $container[GdprService::class] = fn() => new GdprService(
            $container[GdprRepository::class],
            $container[RespondentRepository::class],
        );

        $container[GhostService::class] = fn() => new GhostService(
            $container[GdprRepository::class],
            $container[GhostRepository::class],
            $container[RespondentRepository::class],
            $container[RespondentSurveyRepository::class],
            $container[ResponseRepository::class],
            $container[TextMessageService::class],
        );

        $container[InsightService::class] = fn() => new InsightService(
            $container[InsightRepository::class],
        );

        $container[LogService::class] = fn() => new LogService(
            $container[LogRepository::class],
        );

        $container[MessageService::class] = fn() => new MessageService(
            $container[SwiftMailerAdapter::class],
        );

        $container[OpenService::class] = fn() => new OpenService();

        $container[RespondentService::class] = fn() => new RespondentService(
            $container[GhostRepository::class],
            $container[LogRepository::class],
            $container[RespondentRepository::class],
            $container[RespondentSurveyRepository::class],
            $container[SurveyRepository::class],
            $container[GdprRepository::class],
            $container[AppReviewService::class],
            $container[GdprService::class],
            $container[OpenService::class],
            $container[TextMessageService::class],
            $container[CloudinaryAdapter::class],
            $container[IpToCountryAdapter::class],
            $container['config.hmac.secret'],
        );

        $container[ResponseService::class] = fn() => new ResponseService(
            $container[GhostRepository::class],
            $container[ResponseRepository::class],
            $container[RespondentRepository::class],
            $container[RespondentSurveyRepository::class],
            $container[SurveyRepository::class],
            $container[RespondentService::class],
            $container[TextMessageService::class],
            $container[AfricaTalkingAdapter::class],
            $container[IpToCountryAdapter::class],
            $container['config.hmac.secret'],
        );

        $container[SurveyService::class] = fn() => new SurveyService(
            $container[AirtimeCsvRepository::class],
            $container[GhostRepository::class],
            $container[LogRepository::class],
            $container[RespondentRepository::class],
            $container[RespondentSurveyRepository::class],
            $container[SurveyRepository::class],
            $container[MessageService::class],
            $container[RespondentService::class],
            $container[TextMessageService::class],
            $container[AfricaTalkingAdapter::class],
            $container[AirtimeAdapter::class],
        );

        $container[TextMessageService::class] = fn() => new TextMessageService(
            $container[TextLocalAdapter::class],
        );

        $container[GroupService::class] = fn() => new GroupService(
            $container[GroupRepository::class],
            $container[RespondentRepository::class]
        );
    }
}
