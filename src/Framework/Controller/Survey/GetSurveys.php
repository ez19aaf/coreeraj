<?php

namespace Survey54\Reap\Framework\Controller\Survey;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class GetSurveys extends SurveyController
{
    /**
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    public function execute(ServerRequestInterface $request): ResponseInterface
    {
        $data    = $this->validator->validate($request);
        $history = isset($data['history']) && $data['history'] === 'true';
        $payload = $this->surveyService->list($data['respondentId'], $history);
        return $this->response->withJson($payload, 200);
    }
}
