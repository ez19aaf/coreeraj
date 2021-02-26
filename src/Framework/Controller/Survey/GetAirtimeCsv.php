<?php


namespace Survey54\Reap\Framework\Controller\Survey;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class GetAirtimeCsv extends SurveyController
{

    /**
     * @param ServerRequestInterface $request
     * @return ResponseInterface|null
     */
    public function execute(ServerRequestInterface $request): ResponseInterface
    {
        $logs = $this->surveyService->listAirtimeLogs(0, 100);
        return $this->response->withJson($logs, 200);
    }
}
