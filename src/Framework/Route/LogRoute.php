<?php

namespace Survey54\Reap\Framework\Route;

use Psr\Container\ContainerInterface;
use Slim\App;
use Survey54\Library\Middleware\UuidMiddleware;
use Survey54\Reap\Framework\Controller\Log\GetLogs;
use Survey54\Reap\Framework\Middleware\Admin\GetEndpointMiddleware;

class LogRoute implements RouteInterface
{
    /**
     * @param App $app
     * @param ContainerInterface $container
     */
    public function route(App $app, ContainerInterface $container): void
    {
        $app->group('/logs', function () use ($container) {
            $this->get('', GetLogs::class . ':execute')
                ->add($container[GetEndpointMiddleware::class]); // for admin
        })->add($container[UuidMiddleware::class]);
    }
}
