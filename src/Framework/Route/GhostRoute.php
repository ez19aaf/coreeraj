<?php

namespace Survey54\Reap\Framework\Route;

use Psr\Container\ContainerInterface;
use Slim\App;
use Survey54\Library\Middleware\JsonContentMiddleware;
use Survey54\Library\Middleware\UuidMiddleware;
use Survey54\Reap\Framework\Controller\Ghost\DeleteGhost;
use Survey54\Reap\Framework\Controller\Ghost\GetGhosts;
use Survey54\Reap\Framework\Controller\Ghost\PostGhost;
use Survey54\Reap\Framework\Middleware\Ghost\DeleteGhostMiddleware;
use Survey54\Reap\Framework\Middleware\Ghost\GetGhostsMiddleware;
use Survey54\Reap\Framework\Middleware\Ghost\PostGhostMiddleware;

class GhostRoute implements RouteInterface
{
    /**
     * @param App $app
     * @param ContainerInterface $container
     */
    public function route(App $app, ContainerInterface $container): void
    {
        $app->group('/ghosts', function () use ($container) {
            $this->get('', GetGhosts::class . ':execute')
                ->add($container[GetGhostsMiddleware::class]);
            $this->post('', PostGhost::class . ':execute')
                ->add($container[JsonContentMiddleware::class])
                ->add($container[PostGhostMiddleware::class]);
            $this->delete('/{uuid}', DeleteGhost::class . ':execute')
                ->add($container[JsonContentMiddleware::class])
                ->add($container[DeleteGhostMiddleware::class]);
        })->add($container[UuidMiddleware::class]);
    }
}
