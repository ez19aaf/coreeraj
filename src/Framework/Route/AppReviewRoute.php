<?php

namespace Survey54\Reap\Framework\Route;

use Psr\Container\ContainerInterface;
use Slim\App;
use Survey54\Library\Middleware\JsonContentMiddleware;
use Survey54\Library\Middleware\UuidMiddleware;
use Survey54\Reap\Framework\Controller\AppReviewService\PutAppReview;
use Survey54\Reap\Framework\Middleware\Respondent\PutRespondentMiddleware;

class AppReviewRoute implements RouteInterface
{
    /**
     * @param App $app
     * @param ContainerInterface $container
     */
    public function route(App $app, ContainerInterface $container): void
    {
        $app->group('/app-reviews', function () use ($container) {
            $this->put('/{uuid}', PutAppReview::class . ':execute')
                ->add($container[JsonContentMiddleware::class])
                ->add($container[PutRespondentMiddleware::class]);
        })->add($container[UuidMiddleware::class]);
    }
}
