<?php

namespace Survey54\Reap\Framework\ServiceProvider;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Driver\PDO\MySQL\Driver;
use Exception;
use Slim\Container;
use Survey54\Library\Repository\SqlAdapter;
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
use Survey54\Reap\Domain\AirtimeLogsCsv;
use Survey54\Reap\Domain\AppReview;
use Survey54\Reap\Domain\Gdpr;
use Survey54\Reap\Domain\Ghost;
use Survey54\Reap\Domain\Group;
use Survey54\Reap\Domain\Insight;
use Survey54\Reap\Domain\Log;
use Survey54\Reap\Domain\Respondent;
use Survey54\Reap\Domain\RespondentSurvey;
use Survey54\Reap\Domain\Response;
use Survey54\Reap\Domain\Survey;
use Survey54\Reap\Framework\Exception\Error;

class RepositoryServiceProvider implements ServiceProviderInterface
{
    /**
     * @param Container $container
     */
    public function register(Container $container): void
    {
        try {
            $sqlCon = new Connection($container['config.db'], new Driver());
        } catch (Exception $e) {
            Error::throwError(Error::S54_INTERNAL_SERVER_ERROR, $e->getMessage());
        }

        $container[AirtimeCsvRepository::class]       = fn() => new AirtimeCsvRepository(new SqlAdapter($sqlCon, 'airtime_log'), AirtimeLogsCsv::class);
        $container[AppReviewRepository::class]        = fn() => new AppReviewRepository(new SqlAdapter($sqlCon, 'app_review'), AppReview::class);
        $container[GdprRepository::class]             = fn() => new GdprRepository(new SqlAdapter($sqlCon, 'gdpr'), Gdpr::class);
        $container[GhostRepository::class]            = fn() => new GhostRepository(new SqlAdapter($sqlCon, 'ghost'), Ghost::class);
        $container[InsightRepository::class]          = fn() => new InsightRepository(new SqlAdapter($sqlCon, 'insight'), Insight::class);
        $container[LogRepository::class]              = fn() => new LogRepository(new SqlAdapter($sqlCon, 'log'), Log::class);
        $container[RespondentRepository::class]       = fn() => new RespondentRepository(new SqlAdapter($sqlCon, 'respondent'), Respondent::class);
        $container[RespondentSurveyRepository::class] = fn() => new RespondentSurveyRepository(new SqlAdapter($sqlCon, 'respondent_survey'), RespondentSurvey::class);
        $container[ResponseRepository::class]         = fn() => new ResponseRepository(new SqlAdapter($sqlCon, 'response'), Response::class);
        $container[SurveyRepository::class]           = fn() => new SurveyRepository(new SqlAdapter($sqlCon, 'survey'), Survey::class);
        $container[GroupRepository::class]            = fn() => new GroupRepository(new SqlAdapter($sqlCon, 'group'), Group::class);
    }
}
