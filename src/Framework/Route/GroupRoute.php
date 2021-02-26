<?php

namespace Survey54\Reap\Framework\Route;

use Psr\Container\ContainerInterface;
use Slim\App;
use Survey54\Library\Middleware\JsonContentMiddleware;
use Survey54\Library\Middleware\UuidMiddleware;
use Survey54\Reap\Framework\Controller\Group\DeleteGroup;
use Survey54\Reap\Framework\Controller\Group\OwnGroup;
use Survey54\Reap\Framework\Controller\Group\PostGroup;
use Survey54\Reap\Framework\Controller\Group\PutGroup;
use Survey54\Reap\Framework\Controller\Group\GetGroups;
use Survey54\Reap\Framework\Middleware\Group\DeleteGroupMiddleware;
use Survey54\Reap\Framework\Middleware\Group\GetGroupsMiddleware;
use Survey54\Reap\Framework\Middleware\Group\PostGroupMiddleware;
use Survey54\Reap\Framework\Middleware\Group\PutGroupMiddleware;

class GroupRoute implements RouteInterface
{
    /**
     * @param App $app
     * @param ContainerInterface $container
     */
    public function route(App $app, ContainerInterface $container): void
    {
        $app->group('/groups', function () use ($container) {
            $this->post('', PostGroup::class . ':execute')
                ->add($container[PostGroupMiddleware::class]);
            $this->put('/{uuid}', PutGroup::class . ':execute')
                ->add($container[PutGroupMiddleware::class]);
            $this->get('', GetGroups::class . ':execute')
                ->add($container[GetGroupsMiddleware::class]);
            $this->delete('/delete', DeleteGroup::class . ':execute')
                ->add($container[DeleteGroupMiddleware::class]);
        })->add($container[UuidMiddleware::class]);
    }
}
