<?php

namespace Survey54\Reap\Framework\Controller\Survey;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Respect\Validation\Exceptions\ValidationException;
use Respect\Validation\Validator as v;
use Survey54\Reap\Framework\Exception\Error;

class PostAirtimeForSurvey extends SurveyController
{
    private string $projectRoot;

    /**
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    public function execute(ServerRequestInterface $request): ResponseInterface
    {
        $surveyId = $request->getAttribute('uuid');

        if (($params = $request->getParsedBody()) === null) {
            Error::throwError(Error::S54_INVALID_JSON);
        }

        try {
            v::number()->min(0)->setName('limit')->check($params['limit']);
        } catch (ValidationException $e) {
            Error::throwError(Error::S54_FIELD_VALIDATION_ERROR);
        }

        $limit = $params['limit'];

        chdir($this->projectRoot);
        $cmd = "php src/console.php --task send-airtime --data '{\"surveyId\":\"$surveyId\", \"limit\":$limit}' > /dev/null 2>&1 &";

        exec($cmd, $output, $exit);

        return $this->response->withJson(['exitValue' => $exit], 202);
    }

    /**
     * @param string $directory
     * @return $this
     */
    public function setProjectRoot(string $directory): self
    {
        $this->projectRoot = $directory;
        return $this;
    }
}
