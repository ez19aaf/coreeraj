<?php

namespace Survey54\Reap\Framework\Route;

use Psr\Container\ContainerInterface;
use RKA\Middleware\IpAddress;
use Slim\App;
use Survey54\Library\Middleware\JsonContentMiddleware;
use Survey54\Library\Middleware\UuidMiddleware;
use Survey54\Reap\Framework\Controller\Response\PostCompletedCallback;
use Survey54\Reap\Framework\Controller\Response\PostGetAnalyticsDemographics;
use Survey54\Reap\Framework\Controller\Response\PostGetAnalyticsList;
use Survey54\Reap\Framework\Controller\Response\PostGetCrosstabByDemographics;
use Survey54\Reap\Framework\Controller\Response\PostGetCrosstabByResponse;
use Survey54\Reap\Framework\Controller\Response\PostResponse;
use Survey54\Reap\Framework\Controller\Response\PostResponseSetup;
use Survey54\Reap\Framework\Controller\Response\PostResponseSms;
use Survey54\Reap\Framework\Controller\Response\PostResponseUssd;
use Survey54\Reap\Framework\Middleware\Response\PostResponseMiddleware;
use Survey54\Reap\Framework\Middleware\Survey\GetSurveyMiddleware;

class ResponseRoute implements RouteInterface
{
    /**
     * @param App $app
     * @param ContainerInterface $container
     */
    public function route(App $app, ContainerInterface $container): void
    {
        // Analytics List endpoints. {uuid} is survey UUID
        $app->group('/analytics/{uuid}', function () {
            $this->post('', PostGetAnalyticsList::class . ':execute');
            $this->post('/demographics', PostGetAnalyticsDemographics::class . ':execute');
            $this->post('/crosstab-by-response', PostGetCrosstabByResponse::class . ':execute');
            $this->post('/crosstab-by-demographics', PostGetCrosstabByDemographics::class . ':execute');
        })->add($container[UuidMiddleware::class])
            ->add($container[JsonContentMiddleware::class])
            ->add($container[GetSurveyMiddleware::class]);

        // Responses Post endpoints.
        $app->group('/responses', function () use ($container) {
            $this->post('', PostResponse::class . ':execute')
                ->add($container[PostResponseMiddleware::class]);
            $this->post('/completed-callback', PostCompletedCallback::class . ':execute');
            $this->post('/ussd', PostResponseUssd::class . ':execute');
            $this->post('/sms', PostResponseSms::class . ':execute');
            $this->post('/setup', PostResponseSetup::class . ':execute');
        })->add($container[UuidMiddleware::class])
            ->add($container[JsonContentMiddleware::class])
            ->add($container[IpAddress::class]);
    }
}
