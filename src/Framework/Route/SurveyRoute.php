<?php

namespace Survey54\Reap\Framework\Route;

use Psr\Container\ContainerInterface;
use RKA\Middleware\IpAddress;
use Slim\App;
use Survey54\Library\Middleware\JsonContentMiddleware;
use Survey54\Library\Middleware\UuidMiddleware;
use Survey54\Reap\Framework\Controller\Survey\GetAirtimeLogsForSurvey;
use Survey54\Reap\Framework\Controller\Survey\GetSurvey;
use Survey54\Reap\Framework\Controller\Survey\GetSurveyRespondentEmails;
use Survey54\Reap\Framework\Controller\Survey\GetSurveys;
use Survey54\Reap\Framework\Controller\Survey\GetSurveyStatus;
use Survey54\Reap\Framework\Controller\Survey\PostAirtimeForSurvey;
use Survey54\Reap\Framework\Controller\Survey\PostLaunchSmsUssdAud;
use Survey54\Reap\Framework\Controller\Survey\PostLaunchSurvey;
use Survey54\Reap\Framework\Controller\Survey\PostListSurveysOpen;
use Survey54\Reap\Framework\Controller\Survey\PostSendNotification;
use Survey54\Reap\Framework\Middleware\Admin\GetEndpointMiddleware;
use Survey54\Reap\Framework\Middleware\Survey\GetSurveyMiddleware;
use Survey54\Reap\Framework\Middleware\Survey\GetSurveysMiddleware;
use Survey54\Reap\Framework\Middleware\Survey\PostSurveyMiddleware;
use Survey54\Reap\Framework\Middleware\Survey\PostSurveyUserAllowedMiddleware;

class SurveyRoute implements RouteInterface
{
    /**
     * @param App $app
     * @param ContainerInterface $container
     */
    public function route(App $app, ContainerInterface $container): void
    {
        $app->group('/surveys', function () use ($container) {
            $this->post('/{uuid}/launch', PostLaunchSurvey::class . ':execute')
                ->add($container[PostSurveyMiddleware::class])
                ->add($container[JsonContentMiddleware::class]); // internal: called by core
            $this->post('/{uuid}/send-notification', PostSendNotification::class . ':execute')
                ->add($container[PostSurveyUserAllowedMiddleware::class])
                ->add($container[JsonContentMiddleware::class]);
            $this->get('/{uuid}/airtime-logs', GetAirtimeLogsForSurvey::class . ':execute')
                ->add($container[GetEndpointMiddleware::class]);
            $this->get('/{uuid}', GetSurvey::class . ':execute')
                ->add($container[GetEndpointMiddleware::class]); // Useful for debugging
            $this->post('/{uuid}/airtime', PostAirtimeForSurvey::class . ':execute')
                ->add($container[PostSurveyMiddleware::class])
                ->add($container[JsonContentMiddleware::class]);
            $this->post('/list-open', PostListSurveysOpen::class . ':execute'); // for external
            $this->get('', GetSurveys::class . ':execute')
                ->add($container[GetSurveysMiddleware::class]); // for respondents
            $this->get('/{uuid}/emails', GetSurveyRespondentEmails::class . ':execute')
                ->add($container[GetEndpointMiddleware::class]); // for admin
            $this->get('/{uuid}/status', GetSurveyStatus::class . ':execute')
                ->add($container[GetSurveyMiddleware::class]);
            $this->post('/{uuid}/launch-sms-ussd-aud', PostLaunchSmsUssdAud::class . ':execute')
                ->add($container[JsonContentMiddleware::class]);
        })->add($container[UuidMiddleware::class])
            ->add($container[IpAddress::class]);
    }
}
