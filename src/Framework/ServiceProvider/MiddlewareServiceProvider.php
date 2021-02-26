<?php

namespace Survey54\Reap\Framework\ServiceProvider;

use RKA\Middleware\IpAddress;
use Slim\Container;
use Survey54\Library\Middleware\JsonContentMiddleware;
use Survey54\Library\Middleware\UuidMiddleware;
use Survey54\Reap\Framework\Middleware\Admin\GetEndpointMiddleware;
use Survey54\Reap\Framework\Middleware\Admin\PostEndpointMiddleware;
use Survey54\Reap\Framework\Middleware\Ghost\DeleteGhostMiddleware;
use Survey54\Reap\Framework\Middleware\Ghost\GetGhostsMiddleware;
use Survey54\Reap\Framework\Middleware\Ghost\PostGhostMiddleware;
use Survey54\Reap\Framework\Middleware\Group\DeleteGroupMiddleware;
use Survey54\Reap\Framework\Middleware\Group\GetGroupsMiddleware;
use Survey54\Reap\Framework\Middleware\Group\PostGroupMiddleware;
use Survey54\Reap\Framework\Middleware\Group\PutGroupMiddleware;
use Survey54\Reap\Framework\Middleware\Insight\GetInsightsMiddleware;
use Survey54\Reap\Framework\Middleware\Insight\PostInsightsMiddleware;
use Survey54\Reap\Framework\Middleware\Respondent\AdminPutRespondentMiddleware;
use Survey54\Reap\Framework\Middleware\Respondent\DeleteRespondentMiddleware;
use Survey54\Reap\Framework\Middleware\Respondent\GetRespondentMiddleware;
use Survey54\Reap\Framework\Middleware\Respondent\GetRespondentsMiddleware;
use Survey54\Reap\Framework\Middleware\Respondent\PutRespondentMiddleware;
use Survey54\Reap\Framework\Middleware\Response\PostResponseMiddleware;
use Survey54\Reap\Framework\Middleware\Survey\GetSurveyMiddleware;
use Survey54\Reap\Framework\Middleware\Survey\GetSurveysMiddleware;
use Survey54\Reap\Framework\Middleware\Survey\PostSurveyMiddleware;
use Survey54\Reap\Framework\Middleware\Survey\PostSurveyUserAllowedMiddleware;
use Survey54\Reap\Framework\Middleware\User\GetUserMiddleware;

class MiddlewareServiceProvider implements ServiceProviderInterface
{
    /**
     * @param Container $container
     */
    public function register(Container $container): void
    {
        $container[UuidMiddleware::class]        = fn() => new UuidMiddleware($container);
        $container[JsonContentMiddleware::class] = fn() => new JsonContentMiddleware($container);
        $container[IpAddress::class]             = fn() => new IpAddress(true, []);
        // Admin
        $container[GetEndpointMiddleware::class]  = fn() => new GetEndpointMiddleware($container);
        $container[PostEndpointMiddleware::class] = fn() => new PostEndpointMiddleware($container);
        // Ghost
        $container[DeleteGhostMiddleware::class] = fn() => new DeleteGhostMiddleware($container);
        $container[GetGhostsMiddleware::class]   = fn() => new GetGhostsMiddleware($container);
        $container[PostGhostMiddleware::class]   = fn() => new PostGhostMiddleware($container);
        // Insight
        $container[GetInsightsMiddleware::class]  = fn() => new GetInsightsMiddleware($container);
        $container[PostInsightsMiddleware::class] = fn() => new PostInsightsMiddleware($container);
        // Respondent
        $container[AdminPutRespondentMiddleware::class] = fn() => new AdminPutRespondentMiddleware($container);
        $container[DeleteRespondentMiddleware::class]   = fn() => new DeleteRespondentMiddleware($container);
        $container[GetRespondentMiddleware::class]      = fn() => new GetRespondentMiddleware($container);
        $container[GetRespondentsMiddleware::class]     = fn() => new GetRespondentsMiddleware($container);
        $container[PutRespondentMiddleware::class]      = fn() => new PutRespondentMiddleware($container);
        // Response
        $container[PostResponseMiddleware::class] = fn() => new PostResponseMiddleware($container);
        // Survey
        $container[GetSurveyMiddleware::class]             = fn() => new GetSurveyMiddleware($container);
        $container[GetSurveysMiddleware::class]            = fn() => new GetSurveysMiddleware($container);
        $container[PostSurveyMiddleware::class]            = fn() => new PostSurveyMiddleware($container);
        $container[PostSurveyUserAllowedMiddleware::class] = fn() => new PostSurveyUserAllowedMiddleware($container);
        // User
        $container[GetUserMiddleware::class] = fn() => new GetUserMiddleware($container);
        // Group
        $container[PostGroupMiddleware::class]          = fn() => new PostGroupMiddleware($container);
        $container[PutGroupMiddleware::class]           = fn() => new PutGroupMiddleware($container);
        $container[GetGroupsMiddleware::class]           = fn() => new GetGroupsMiddleware($container);
        $container[DeleteGroupMiddleware::class]        = fn() => new DeleteGroupMiddleware($container);
    }
}
