<?php

namespace Survey54\Reap\Framework\ServiceProvider;

use Slim\App;
use Survey54\Reap\Framework\Route\AirtimeRoute;
use Survey54\Reap\Framework\Route\AppReviewRoute;
use Survey54\Reap\Framework\Route\FilesRoute;
use Survey54\Reap\Framework\Route\GhostRoute;
use Survey54\Reap\Framework\Route\GroupRoute;
use Survey54\Reap\Framework\Route\InsightRoute;
use Survey54\Reap\Framework\Route\LogRoute;
use Survey54\Reap\Framework\Route\OpenRoute;
use Survey54\Reap\Framework\Route\RespondentRoute;
use Survey54\Reap\Framework\Route\ResponseRoute;
use Survey54\Reap\Framework\Route\SurveyRoute;
use Survey54\Reap\Framework\Route\UserRoute;

class RouteServiceProvider
{
    /**
     * @param App $app
     */
    public function registerRoutes(App $app): void
    {
        $container = $app->getContainer();

        (new AirtimeRoute())->route($app, $container);
        (new AppReviewRoute())->route($app, $container);
        (new FilesRoute())->route($app, $container);
        (new GhostRoute())->route($app, $container);
        (new InsightRoute())->route($app, $container);
        (new LogRoute())->route($app, $container);
        (new OpenRoute())->route($app, $container);
        (new RespondentRoute())->route($app, $container);
        (new ResponseRoute())->route($app, $container);
        (new SurveyRoute())->route($app, $container);
        (new UserRoute())->route($app, $container);
        (new GroupRoute())->route($app, $container);
    }
}
