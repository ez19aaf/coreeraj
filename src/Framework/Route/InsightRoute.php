<?php

namespace Survey54\Reap\Framework\Route;

use Psr\Container\ContainerInterface;
use Slim\App;
use Survey54\Library\Middleware\JsonContentMiddleware;
use Survey54\Library\Middleware\UuidMiddleware;
use Survey54\Reap\Framework\Controller\Insight\GetInsights;
use Survey54\Reap\Framework\Controller\Insight\PostInsights;
use Survey54\Reap\Framework\Middleware\Insight\GetInsightsMiddleware;
use Survey54\Reap\Framework\Middleware\Insight\PostInsightsMiddleware;

class InsightRoute implements RouteInterface
{
    /**
     * @param App $app
     * @param ContainerInterface $container
     */
    public function route(App $app, ContainerInterface $container): void
    {
        $app->group('/insights', function () use ($container) {
            $this->get('', GetInsights::class . ':execute')
                ->add($container[GetInsightsMiddleware::class]);

            $this->post('', PostInsights::class . ':execute')
                ->add($container[PostInsightsMiddleware::class])->add($container[JsonContentMiddleware::class]);
        })->add($container[UuidMiddleware::class]);
    }
}
