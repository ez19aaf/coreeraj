<?php

namespace Survey54\Reap\Framework\Controller\Survey;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class GetSurveyStatus extends SurveyController
{
    /**
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    public function execute(ServerRequestInterface $request): ResponseInterface
    {
        $surveyId = $request->getAttribute('uuid');
        $payload  = $this->surveyService->getStatus($surveyId);
        return $this->response->withJson($payload, 200);
    }
}
