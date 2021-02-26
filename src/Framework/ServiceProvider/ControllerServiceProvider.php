<?php

namespace Survey54\Reap\Framework\ServiceProvider;

use Slim\Container;
use Survey54\Reap\Application\AppReviewService;
use Survey54\Reap\Application\FileService;
use Survey54\Reap\Application\GhostService;
use Survey54\Reap\Application\GroupService;
use Survey54\Reap\Application\InsightService;
use Survey54\Reap\Application\LogService;
use Survey54\Reap\Application\OpenService;
use Survey54\Reap\Application\RespondentService;
use Survey54\Reap\Application\ResponseService;
use Survey54\Reap\Application\SurveyService;
use Survey54\Reap\Framework\Controller\AppReviewService\PutAppReview;
use Survey54\Reap\Framework\Controller\Files\GetPdf;
use Survey54\Reap\Framework\Controller\Files\GetPowerpoint;
use Survey54\Reap\Framework\Controller\Files\GetSpreadSheet;
use Survey54\Reap\Framework\Controller\Ghost\DeleteGhost;
use Survey54\Reap\Framework\Controller\Ghost\GetGhosts;
use Survey54\Reap\Framework\Controller\Ghost\PostGhost;
use Survey54\Reap\Framework\Controller\Group\DeleteGroup;
use Survey54\Reap\Framework\Controller\Group\GetGroups;
use Survey54\Reap\Framework\Controller\Group\PostGroup;
use Survey54\Reap\Framework\Controller\Group\PutGroup;
use Survey54\Reap\Framework\Controller\Insight\GetInsights;
use Survey54\Reap\Framework\Controller\Insight\PostInsights;
use Survey54\Reap\Framework\Controller\Log\GetLogs;
use Survey54\Reap\Framework\Controller\Open\GetAgeGroups;
use Survey54\Reap\Framework\Controller\Open\GetEmployments;
use Survey54\Reap\Framework\Controller\Open\GetGenders;
use Survey54\Reap\Framework\Controller\Open\GetLsmGroups;
use Survey54\Reap\Framework\Controller\Open\GetRaces;
use Survey54\Reap\Framework\Controller\Open\LsmCalculator;
use Survey54\Reap\Framework\Controller\Respondent\ActivateRespondent;
use Survey54\Reap\Framework\Controller\Respondent\AddLsm;
use Survey54\Reap\Framework\Controller\Respondent\ChangePassword;
use Survey54\Reap\Framework\Controller\Respondent\DeactivateRespondent;
use Survey54\Reap\Framework\Controller\Respondent\DeleteRespondent;
use Survey54\Reap\Framework\Controller\Respondent\ForgotPassword;
use Survey54\Reap\Framework\Controller\Respondent\GetRespondent;
use Survey54\Reap\Framework\Controller\Respondent\GetRespondentsDistribution;
use Survey54\Reap\Framework\Controller\Respondent\GetRespondentSurveyHistory;
use Survey54\Reap\Framework\Controller\Respondent\GetRespondentTotalEarnings;
use Survey54\Reap\Framework\Controller\Respondent\PostCheckMobile;
use Survey54\Reap\Framework\Controller\Respondent\PostLogin;
use Survey54\Reap\Framework\Controller\Respondent\PostRefreshToken;
use Survey54\Reap\Framework\Controller\Respondent\PostRespondent;
use Survey54\Reap\Framework\Controller\Respondent\PostVerifyRespondent;
use Survey54\Reap\Framework\Controller\Respondent\PutProfileImage;
use Survey54\Reap\Framework\Controller\Respondent\PutRespondent;
use Survey54\Reap\Framework\Controller\Respondent\RemoveProfileImage;
use Survey54\Reap\Framework\Controller\Respondent\SendNewVerificationCode;
use Survey54\Reap\Framework\Controller\Respondent\SendText;
use Survey54\Reap\Framework\Controller\Respondent\SetPassword;
use Survey54\Reap\Framework\Controller\Response\PostCompletedCallback;
use Survey54\Reap\Framework\Controller\Response\PostGetAnalyticsDemographics;
use Survey54\Reap\Framework\Controller\Response\PostGetAnalyticsList;
use Survey54\Reap\Framework\Controller\Response\PostGetCrosstabByDemographics;
use Survey54\Reap\Framework\Controller\Response\PostGetCrosstabByResponse;
use Survey54\Reap\Framework\Controller\Response\PostResponse;
use Survey54\Reap\Framework\Controller\Response\PostResponseSetup;
use Survey54\Reap\Framework\Controller\Response\PostResponseSms;
use Survey54\Reap\Framework\Controller\Response\PostResponseUssd;
use Survey54\Reap\Framework\Controller\Survey\GetAirtimeCsv;
use Survey54\Reap\Framework\Controller\Survey\GetAirtimeLogsForSurvey;
use Survey54\Reap\Framework\Controller\Survey\GetSurvey;
use Survey54\Reap\Framework\Controller\Survey\GetSurveyRespondentEmails;
use Survey54\Reap\Framework\Controller\Survey\GetSurveys;
use Survey54\Reap\Framework\Controller\Survey\GetSurveyStats;
use Survey54\Reap\Framework\Controller\Survey\GetSurveyStatus;
use Survey54\Reap\Framework\Controller\Survey\PostAirtimeCsv;
use Survey54\Reap\Framework\Controller\Survey\PostAirtimeForSurvey;
use Survey54\Reap\Framework\Controller\Survey\PostLaunchSmsUssdAud;
use Survey54\Reap\Framework\Controller\Survey\PostLaunchSurvey;
use Survey54\Reap\Framework\Controller\Survey\PostListSurveysOpen;
use Survey54\Reap\Framework\Controller\Survey\PostSendNotification;
use Survey54\Reap\Framework\Validator\AppReview\PutAppReviewValidator;
use Survey54\Reap\Framework\Validator\Ghost\GetGhostsValidator;
use Survey54\Reap\Framework\Validator\Ghost\PostGhostValidator;
use Survey54\Reap\Framework\Validator\Group\GetGroupsValidator;
use Survey54\Reap\Framework\Validator\Group\PostGroupValidator;
use Survey54\Reap\Framework\Validator\Insight\GetInsightsValidator;
use Survey54\Reap\Framework\Validator\Insight\PostInsightsValidator;
use Survey54\Reap\Framework\Validator\Log\GetLogsValidator;
use Survey54\Reap\Framework\Validator\Respondent\ChangePasswordValidator;
use Survey54\Reap\Framework\Validator\Respondent\PostCheckMobileValidator;
use Survey54\Reap\Framework\Validator\Respondent\PostRefreshTokenValidator;
use Survey54\Reap\Framework\Validator\Respondent\PostRespondentValidator;
use Survey54\Reap\Framework\Validator\Respondent\PostVerifyRespondentValidator;
use Survey54\Reap\Framework\Validator\Respondent\PutProfileImageValidator;
use Survey54\Reap\Framework\Validator\Respondent\PutRespondentValidator;
use Survey54\Reap\Framework\Validator\Respondent\SendNewVerificationCodeValidator;
use Survey54\Reap\Framework\Validator\Respondent\SendTextValidator;
use Survey54\Reap\Framework\Validator\Respondent\TypeValueValidator;
use Survey54\Reap\Framework\Validator\Response\AnalyticsValidator;
use Survey54\Reap\Framework\Validator\Response\CrossTabsValidator;
use Survey54\Reap\Framework\Validator\Response\CrossTabsWtDemographicsValidator;
use Survey54\Reap\Framework\Validator\Response\PostResponseSetupValidator;
use Survey54\Reap\Framework\Validator\Response\PostResponseValidator;
use Survey54\Reap\Framework\Validator\Survey\GetSurveysValidator;
use Survey54\Reap\Framework\Validator\Survey\PostAirtimeCsvValidator;
use Survey54\Reap\Framework\Validator\Survey\PostLaunchSmsUssdAudValidator;
use Survey54\Reap\Framework\Validator\Survey\PostListSurveysOpenValidator;

class ControllerServiceProvider implements ServiceProviderInterface
{
    /**
     * @param Container $container
     */
    public function register(Container $container): void
    {
        $projectRoot = '/var/www/reap/current'; // root of deployment
        // Analytics
        $container[PostGetAnalyticsList::class]          = fn() => new PostGetAnalyticsList($container[ResponseService::class], new AnalyticsValidator());
        $container[PostGetAnalyticsDemographics::class]  = fn() => new PostGetAnalyticsDemographics($container[ResponseService::class], new AnalyticsValidator());
        $container[PostGetCrosstabByResponse::class]     = fn() => new PostGetCrosstabByResponse($container[ResponseService::class], new CrossTabsValidator());
        $container[PostGetCrosstabByDemographics::class] = fn() => new PostGetCrosstabByDemographics($container[ResponseService::class], new CrossTabsWtDemographicsValidator());
        // AppReview
        $container[PutAppReview::class] = fn() => new PutAppReview($container[AppReviewService::class], new PutAppReviewValidator());
        // Files
        $container[GetSpreadSheet::class] = fn() => new GetSpreadSheet($container[FileService::class]);
        $container[GetPdf::class]         = fn() => new GetPdf($container[FileService::class]);
        $container[GetPowerpoint::class]  = fn() => new GetPowerpoint($container[FileService::class]);
        // Ghost
        $container[DeleteGhost::class] = fn() => new DeleteGhost($container[GhostService::class]);
        $container[GetGhosts::class]   = fn() => new GetGhosts($container[GhostService::class], new GetGhostsValidator());
        $container[PostGhost::class]   = fn() => new PostGhost($container[GhostService::class], new PostGhostValidator());
        // Insight
        $container[GetInsights::class]  = fn() => new GetInsights($container[InsightService::class], new GetInsightsValidator());
        $container[PostInsights::class] = fn() => new PostInsights($container[InsightService::class], new PostInsightsValidator());
        // Log
        $container[GetLogs::class] = fn() => new GetLogs($container[LogService::class], new GetLogsValidator());
        // Open
        $container[GetAgeGroups::class]   = fn() => new GetAgeGroups($container[OpenService::class]);
        $container[GetEmployments::class] = fn() => new GetEmployments($container[OpenService::class]);
        $container[GetGenders::class]     = fn() => new GetGenders($container[OpenService::class]);
        $container[GetLsmGroups::class]   = fn() => new GetLsmGroups($container[OpenService::class]);
        $container[GetRaces::class]       = fn() => new GetRaces($container[OpenService::class]);
        $container[LsmCalculator::class]  = fn() => new LsmCalculator($container[OpenService::class]);
        // Respondent
        $container[ActivateRespondent::class]         = fn() => new ActivateRespondent($container[RespondentService::class]);
        $container[AddLsm::class]                     = fn() => new AddLsm($container[RespondentService::class]);
        $container[ChangePassword::class]             = fn() => new ChangePassword($container[RespondentService::class], new ChangePasswordValidator());
        $container[DeactivateRespondent::class]       = fn() => new DeactivateRespondent($container[RespondentService::class]);
        $container[DeleteRespondent::class]           = fn() => new DeleteRespondent($container[RespondentService::class]);
        $container[ForgotPassword::class]             = fn() => new ForgotPassword($container[RespondentService::class], new TypeValueValidator());
        $container[GetRespondent::class]              = fn() => new GetRespondent($container[RespondentService::class]);
        $container[GetRespondentsDistribution::class] = fn() => new GetRespondentsDistribution($container[RespondentService::class]);
        $container[GetRespondentSurveyHistory::class] = fn() => new GetRespondentSurveyHistory($container[RespondentService::class]);
        $container[GetRespondentTotalEarnings::class] = fn() => new GetRespondentTotalEarnings($container[RespondentService::class]);
        $container[PostCheckMobile::class]            = fn() => new PostCheckMobile($container[RespondentService::class], new PostCheckMobileValidator());
        $container[PostLogin::class]                  = fn() => new PostLogin($container[RespondentService::class], new TypeValueValidator());
        $container[PostRefreshToken::class]           = fn() => new PostRefreshToken($container[RespondentService::class], new PostRefreshTokenValidator());
        $container[PostRespondent::class]             = fn() => new PostRespondent($container[RespondentService::class], new PostRespondentValidator());
        $container[PostVerifyRespondent::class]       = fn() => new PostVerifyRespondent($container[RespondentService::class], new PostVerifyRespondentValidator());
        $container[PutProfileImage::class]            = fn() => new PutProfileImage($container[RespondentService::class], new PutProfileImageValidator());
        $container[PutRespondent::class]              = fn() => new PutRespondent($container[RespondentService::class], new PutRespondentValidator());
        $container[RemoveProfileImage::class]         = fn() => new RemoveProfileImage($container[RespondentService::class]);
        $container[SendNewVerificationCode::class]    = fn() => new SendNewVerificationCode($container[RespondentService::class], new SendNewVerificationCodeValidator());
        $container[SendText::class]                   = fn() => new SendText($container[RespondentService::class], new SendTextValidator());
        $container[SetPassword::class]                = fn() => new SetPassword($container[RespondentService::class]);
        // Response
        $container[PostCompletedCallback::class] = fn() => new PostCompletedCallback($container[ResponseService::class]);
        $container[PostResponse::class]          = fn() => new PostResponse($container[ResponseService::class], new PostResponseValidator());
        $container[PostResponseSetup::class]     = fn() => new PostResponseSetup($container[ResponseService::class], new PostResponseSetupValidator());
        $container[PostResponseUssd::class]      = fn() => new PostResponseUssd($container[ResponseService::class]);
        $container[PostResponseSms::class]       = fn() => new PostResponseSms($container[ResponseService::class]);
        // Survey
        $container[GetSurveyRespondentEmails::class] = fn() => new GetSurveyRespondentEmails($container[SurveyService::class]);
        $container[GetSurvey::class]                 = fn() => new GetSurvey($container[SurveyService::class]);
        $container[GetSurveyStatus::class]           = fn() => new GetSurveyStatus($container[SurveyService::class]);
        $container[GetSurveyStats::class]            = fn() => new GetSurveyStats($container[SurveyService::class]);
        $container[GetSurveys::class]                = fn() => new GetSurveys($container[SurveyService::class], new GetSurveysValidator());
        $container[PostListSurveysOpen::class]       = fn() => new PostListSurveysOpen($container[SurveyService::class], new PostListSurveysOpenValidator());
        $container[PostLaunchSurvey::class]          = fn() => new PostLaunchSurvey($container[SurveyService::class]);
        $container[PostSendNotification::class]      = fn() => new PostSendNotification($container[SurveyService::class]);
        $container[GetAirtimeLogsForSurvey::class]   = fn() => new GetAirtimeLogsForSurvey($container[SurveyService::class]);
        $container[PostAirtimeForSurvey::class]      = fn() => (new PostAirtimeForSurvey($container[SurveyService::class]))->setProjectRoot($projectRoot);
        $container[PostLaunchSmsUssdAud::class]      = fn() => new PostLaunchSmsUssdAud($container[SurveyService::class], new PostLaunchSmsUssdAudValidator());
        //Airtime
        $container[GetAirtimeCsv::class]  = fn() => new GetAirtimeCsv($container[SurveyService::class]);
        $container[PostAirtimeCsv::class] = fn() => (new PostAirtimeCsv($container[SurveyService::class], new PostAirtimeCsvValidator()))->setProjectRoot($projectRoot);
        // Group
        $container[PostGroup::class]    = fn() => new PostGroup($container[GroupService::class], new PostGroupValidator());
        $container[PutGroup::class]     = fn() => new PutGroup($container[GroupService::class], new PostGroupValidator());
        $container[DeleteGroup::class]  = fn() => new DeleteGroup($container[GroupService::class]);
        $container[GetGroups::class]    = fn() => new GetGroups($container[GroupService::class], new GetGroupsValidator());
    }
}
