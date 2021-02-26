<?php

namespace Survey54\Reap\Framework\Controller\Respondent;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class GetRespondentSurveyHistory extends RespondentController
{
    /**
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    public function execute(ServerRequestInterface $request): ResponseInterface
    {
        $respondentId = $request->getAttribute('uuid');
        $payload      = $this->respondentService->getSurveyHistory($respondentId);
        return $this->response->withJson($payload, 200);
    }
}
