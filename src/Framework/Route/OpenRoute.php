<?php

namespace Survey54\Reap\Framework\Route;

use Psr\Container\ContainerInterface;
use Slim\App;
use Survey54\Library\Middleware\UuidMiddleware;
use Survey54\Reap\Framework\Controller\Open\GetAgeGroups;
use Survey54\Reap\Framework\Controller\Open\GetEmployments;
use Survey54\Reap\Framework\Controller\Open\GetGenders;
use Survey54\Reap\Framework\Controller\Open\GetLsmGroups;
use Survey54\Reap\Framework\Controller\Open\GetRaces;
use Survey54\Reap\Framework\Controller\Open\LsmCalculator;

class OpenRoute implements RouteInterface
{
    /**
     * @param App $app
     * @param ContainerInterface $container
     */
    public function route(App $app, ContainerInterface $container): void
    {
        $app->group('/open', function () {
            $this->get('/age-groups', GetAgeGroups::class . ':execute');
            $this->get('/employments', GetEmployments::class . ':execute');
            $this->get('/genders', GetGenders::class . ':execute');
            $this->get('/lsm-groups', GetLsmGroups::class . ':execute');
            $this->get('/lsm-calculator', LsmCalculator::class . ':execute');
            $this->get('/races', GetRaces::class . ':execute');
        })->add($container[UuidMiddleware::class]);
    }
}
