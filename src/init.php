<?php

use Dotenv\Dotenv;
use Slim\App;
use Slim\Container;
use Survey54\Reap\Framework\ServiceProvider\AdapterServiceProvider;
use Survey54\Reap\Framework\ServiceProvider\ApplicationServiceProvider;
use Survey54\Reap\Framework\ServiceProvider\ControllerServiceProvider;
use Survey54\Reap\Framework\ServiceProvider\MiddlewareServiceProvider;
use Survey54\Reap\Framework\ServiceProvider\RepositoryServiceProvider;
use Survey54\Reap\Framework\ServiceProvider\RouteServiceProvider;

$env = Dotenv::createImmutable(dirname(__DIR__));
$env->load();

$config = require __DIR__ . '/../config/App.php';
$app    = new App($config);
/** @var Container $container */
$container = $app->getContainer();

$CHARACTERS         = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
$SMS_GLOBAL_API_KEY = '';
$SMS_GLOBAL_SECRET  = '';

require __DIR__ . '/../config/ContainerConfig.php';

const SAMPLE_SURVEY_ID = '32554e42-9fec-450f-9c77-1a9461979e40';
const SAMPLE_USER_ID   = '0c0fed5a-2a09-43db-9e2f-b5467a67f7fc';
const SAMPLE_GROUP_ID  = '199ca565-7a32-4701-991a-b46af270e633';

(new AdapterServiceProvider())->register($container);
(new ApplicationServiceProvider())->register($container);
(new ControllerServiceProvider())->register($container);
(new MiddlewareServiceProvider())->register($container);
(new RepositoryServiceProvider())->register($container);
(new RouteServiceProvider())->registerRoutes($app);

$app->run();
