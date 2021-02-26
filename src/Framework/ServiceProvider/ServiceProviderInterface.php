<?php

namespace Survey54\Reap\Framework\ServiceProvider;

use Slim\Container;

interface ServiceProviderInterface
{
    /**
     * @param Container $container
     */
    public function register(Container $container): void;
}
