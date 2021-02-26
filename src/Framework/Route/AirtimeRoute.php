<?php


namespace Survey54\Reap\Framework\Route;

use Psr\Container\ContainerInterface;
use Slim\App;
use Survey54\Library\Middleware\JsonContentMiddleware;
use Survey54\Library\Middleware\UuidMiddleware;
use Survey54\Reap\Framework\Controller\Survey\GetAirtimeCsv;
use Survey54\Reap\Framework\Controller\Survey\PostAirtimeCsv;
use Survey54\Reap\Framework\Middleware\Admin\GetEndpointMiddleware;
use Survey54\Reap\Framework\Middleware\Admin\PostEndpointMiddleware;

class AirtimeRoute implements RouteInterface
{

    /**
     * @param App $app
     * @param ContainerInterface $container
     */
    public function route(App $app, ContainerInterface $container): void
    {
        $app->group('/airtime', function () use ($container) {
            $this->post('', PostAirtimeCsv::class . ':execute')
                ->add($container[PostEndpointMiddleware::class])
                ->add($container[JsonContentMiddleware::class]);
            $this->get('/logs', GetAirtimeCsv::class . ':execute')
                ->add($container[GetEndpointMiddleware::class]);
        })->add($container[UuidMiddleware::class]);
    }
}
