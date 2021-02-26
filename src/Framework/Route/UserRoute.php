<?php

namespace Survey54\Reap\Framework\Route;

use Psr\Container\ContainerInterface;
use Slim\App;
use Survey54\Library\Middleware\UuidMiddleware;
use Survey54\Reap\Framework\Controller\Survey\GetSurveyStats;
use Survey54\Reap\Framework\Middleware\User\GetUserMiddleware;

class UserRoute implements RouteInterface
{
    /**
     * @param App $app
     * @param ContainerInterface $container
     */
    public function route(App $app, ContainerInterface $container): void
    {
        $app->group('/users', function () use ($container) {
            $this->get('/{uuid}/survey-stats', GetSurveyStats::class . ':execute')
                ->add($container[GetUserMiddleware::class]);
        })->add($container[UuidMiddleware::class]);
    }
}
