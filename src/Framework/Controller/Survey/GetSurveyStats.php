<?php

namespace Survey54\Reap\Framework\Controller\Survey;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class GetSurveyStats extends SurveyController
{
    /**
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    public function execute(ServerRequestInterface $request): ResponseInterface
    {
        //Route: /users/{uuid}/survey-stats
        $userId = $request->getAttribute('uuid');
        $result = $this->surveyService->getSurveyStats($userId);

        return $this->response->withJson($result, 200);
    }
}
