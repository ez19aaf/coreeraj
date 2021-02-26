<?php

namespace Survey54\Reap\Framework\Controller\Survey;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class PostLaunchSurvey extends SurveyController
{
    /**
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    public function execute(ServerRequestInterface $request): ResponseInterface
    {
        $surveyId = $request->getAttribute('uuid');
        $this->surveyService->launchWebSurvey($surveyId);
        return $this->response->withStatus(202);
    }
}
