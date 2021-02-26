<?php

namespace Survey54\Reap\Framework\Controller\Survey;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class PostLaunchSmsUssdAud extends SurveyController
{
    /**
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    public function execute(ServerRequestInterface $request): ResponseInterface
    {
        $surveyId = $request->getAttribute('uuid');
        $params   = $this->validator->validate($request);
        $this->surveyService->launchSmsUssdAudSurvey($surveyId, $params);
        return $this->response->withStatus(202);
    }
}
