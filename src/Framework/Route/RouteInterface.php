<?php

namespace Survey54\Reap\Framework\Route;

use Psr\Container\ContainerInterface;
use Slim\App;

interface RouteInterface
{
    /**
     * @param App $app
     * @param ContainerInterface $container
     */
    public function route(App $app, ContainerInterface $container): void;
}
