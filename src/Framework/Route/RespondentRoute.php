<?php

namespace Survey54\Reap\Framework\Route;

use Psr\Container\ContainerInterface;
use RKA\Middleware\IpAddress;
use Slim\App;
use Survey54\Library\Middleware\JsonContentMiddleware;
use Survey54\Library\Middleware\UuidMiddleware;
use Survey54\Reap\Framework\Controller\Respondent\ActivateRespondent;
use Survey54\Reap\Framework\Controller\Respondent\AddLsm;
use Survey54\Reap\Framework\Controller\Respondent\ChangePassword;
use Survey54\Reap\Framework\Controller\Respondent\DeactivateRespondent;
use Survey54\Reap\Framework\Controller\Respondent\DeleteRespondent;
use Survey54\Reap\Framework\Controller\Respondent\ForgotPassword;
use Survey54\Reap\Framework\Controller\Respondent\GetRespondent;
use Survey54\Reap\Framework\Controller\Respondent\GetRespondentsDistribution;
use Survey54\Reap\Framework\Controller\Respondent\GetRespondentSurveyHistory;
use Survey54\Reap\Framework\Controller\Respondent\GetRespondentTotalEarnings;
use Survey54\Reap\Framework\Controller\Respondent\PostCheckMobile;
use Survey54\Reap\Framework\Controller\Respondent\PostLogin;
use Survey54\Reap\Framework\Controller\Respondent\PostRefreshToken;
use Survey54\Reap\Framework\Controller\Respondent\PostRespondent;
use Survey54\Reap\Framework\Controller\Respondent\PostVerifyRespondent;
use Survey54\Reap\Framework\Controller\Respondent\PutProfileImage;
use Survey54\Reap\Framework\Controller\Respondent\PutRespondent;
use Survey54\Reap\Framework\Controller\Respondent\RemoveProfileImage;
use Survey54\Reap\Framework\Controller\Respondent\SendNewVerificationCode;
use Survey54\Reap\Framework\Controller\Respondent\SendText;
use Survey54\Reap\Framework\Controller\Respondent\SetPassword;
use Survey54\Reap\Framework\Middleware\Admin\PostEndpointMiddleware;
use Survey54\Reap\Framework\Middleware\Respondent\AdminPutRespondentMiddleware;
use Survey54\Reap\Framework\Middleware\Respondent\DeleteRespondentMiddleware;
use Survey54\Reap\Framework\Middleware\Respondent\GetRespondentMiddleware;
use Survey54\Reap\Framework\Middleware\Respondent\PutRespondentMiddleware;

class RespondentRoute implements RouteInterface
{
    /**
     * @param App $app
     * @param ContainerInterface $container
     */
    public function route(App $app, ContainerInterface $container): void
    {
        // Open
        $app->group('/respondents', function () {
            $this->get('/distribution', GetRespondentsDistribution::class . ':execute');
        })->add($container[UuidMiddleware::class])
            ->add($container[IpAddress::class]);

        // Get respondent perm
        $app->group('/respondents', function () {
            $this->get('/{uuid}', GetRespondent::class . ':execute');
            $this->get('/{uuid}/survey-history', GetRespondentSurveyHistory::class . ':execute');
            $this->get('/{uuid}/total-earnings', GetRespondentTotalEarnings::class . ':execute');
        })->add($container[UuidMiddleware::class])
            ->add($container[IpAddress::class])
            ->add($container[GetRespondentMiddleware::class]);

        // JSON content
        $app->group('/respondents', function () {
            $this->post('', PostRespondent::class . ':execute'); // register
            $this->post('/forgot-password', ForgotPassword::class . ':execute');
            $this->post('/verify', PostVerifyRespondent::class . ':execute');
            $this->post('/{uuid}/set-password', SetPassword::class . ':execute');
            $this->post('/login', PostLogin::class . ':execute');
            $this->post('/refresh-token', PostRefreshToken::class . ':execute');
            $this->post('/send-new-verification', SendNewVerificationCode::class . ':execute');
            $this->post('/check-mobile', PostCheckMobile::class . ':execute');
            $this->post('/{uuid}/lsm', AddLsm::class . ':execute');
        })->add($container[UuidMiddleware::class])
            ->add($container[IpAddress::class])
            ->add($container[JsonContentMiddleware::class]);

        // Update respondent perm
        $app->group('/respondents', function () {
            $this->put('/{uuid}', PutRespondent::class . ':execute');
            $this->put('/{uuid}/change-password', ChangePassword::class . ':execute');
            $this->put('/{uuid}/upload-photo', PutProfileImage::class . ':execute');
            $this->delete('/{uuid}/remove-photo', RemoveProfileImage::class . ':execute');
        })->add($container[UuidMiddleware::class])
            ->add($container[IpAddress::class])
            ->add($container[JsonContentMiddleware::class])
            ->add($container[PutRespondentMiddleware::class]);

        // Custom perms
        $app->group('/respondents', function () use ($container) {
            $this->post('/{uuid}/activate', ActivateRespondent::class . ':execute')
                ->add($container[AdminPutRespondentMiddleware::class]);
            $this->post('/{uuid}/deactivate', DeactivateRespondent::class . ':execute')
                ->add($container[AdminPutRespondentMiddleware::class]);
            $this->delete('/{uuid}', DeleteRespondent::class . ':execute')
                ->add($container[DeleteRespondentMiddleware::class]);
            $this->post('/send-text', SendText::class . ':execute')
                ->add($container[PostEndpointMiddleware::class]);
        })->add($container[UuidMiddleware::class])
            ->add($container[IpAddress::class])
            ->add($container[JsonContentMiddleware::class]);
    }
}
