<?php

namespace Survey54\Reap\Framework\Controller\Respondent;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Survey54\Library\Validation\Validator;
use Survey54\Reap\Framework\Exception\Error;

class SetPassword extends RespondentController
{
    /**
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    public function execute(ServerRequestInterface $request): ResponseInterface
    {
        $uuid     = $request->getAttribute('uuid');
        $password = $request->getHeader('Authorization')[0] ?? null;

        if (!$password) {
            Error::throwError(Error::S54_PASSWORD_MISSING);
        }

        if (!preg_match(Validator::PASSWORD_REGEX, $password)) {
            Error::throwError(Error::S54_PASSWORD_RULE_ERROR);
        }

        $response = $this->respondentService->setPasswordAndLogin($uuid, $password);

        return $this->response->withJson($response, 202);
    }
}
