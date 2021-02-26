<?php

namespace Survey54\Reap\Framework\Controller\Survey;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Http\Stream;

class GetSurveyRespondentEmails extends SurveyController
{
    /**
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    public function execute(ServerRequestInterface $request): ResponseInterface
    {
        $surveyId = $request->getAttribute('uuid');
        $emails   = $this->surveyService->getRespondentEmails($surveyId);
        if (!$emails) {
            return $this->response->withJson('No result Found', 200);
        }
        // Once the memory usage hits 5mb store the data held in a temporary file.
        $stream = fopen('php://temp/maxmemory:' . (5 * 1024 * 1024), 'wb');
        fputcsv($stream, ['Emails']);
        foreach ($emails as $email) {
            fputcsv($stream, [$email['email']], ';');
        }
        rewind($stream);

        $this->response = $this->response->withHeader('Content-Type', 'text/csv');
        $this->response = $this->response->withHeader('Content-Disposition', "attachment; filename=data.csv");

        $this->response->withBody(new Stream($stream));
        return $this->response;
    }
}
