<?php


namespace Survey54\Reap\Framework\Route;

use Psr\Container\ContainerInterface;
use Slim\App;
use Survey54\Library\Middleware\UuidMiddleware;
use Survey54\Reap\Framework\Controller\Files\GetPdf;
use Survey54\Reap\Framework\Controller\Files\GetPowerpoint;
use Survey54\Reap\Framework\Controller\Files\GetSpreadSheet;
use Survey54\Reap\Framework\Middleware\Survey\GetSurveyMiddleware;

class FilesRoute implements RouteInterface
{
    /**
     * @param App $app
     * @param ContainerInterface $container
     */

    public function route(App $app, ContainerInterface $container): void
    {
        $app->group('/file', function () use ($container) {
            $this->get('/surveys/{uuid}/spreadsheet', GetSpreadSheet::class . ':execute')
                ->add($container[GetSurveyMiddleware::class]);
            $this->get('/surveys/{uuid}/pdf', GetPdf::class . ':execute')
                ->add($container[GetSurveyMiddleware::class]);
            $this->get('/surveys/{uuid}/slides', GetPowerpoint::class . ':execute')
                ->add($container[GetSurveyMiddleware::class]);
        })->add($container[UuidMiddleware::class]);
    }
}
