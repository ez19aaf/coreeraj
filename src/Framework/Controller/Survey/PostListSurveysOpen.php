<?php

namespace Survey54\Reap\Framework\Controller\Survey;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class PostListSurveysOpen extends SurveyController
{
    /**
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    public function execute(ServerRequestInterface $request): ResponseInterface
    {
        $data    = $this->validator->validate($request);
        $payload = $this->surveyService->listOpen($data);
        return $this->response->withJson($payload, 200);
    }
}
