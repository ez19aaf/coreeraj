<?php

namespace Survey54\Reap\Framework\Controller\Survey;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class GetSurvey extends SurveyController
{
    /**
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    public function execute(ServerRequestInterface $request): ResponseInterface
    {
        $uuid   = $request->getAttribute('uuid');
        $result = $this->surveyService->find($uuid);
        return $this->response->withJson($result, 200);
    }
}
