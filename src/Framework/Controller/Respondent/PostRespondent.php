<?php

namespace Survey54\Reap\Framework\Controller\Respondent;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Survey54\Library\Validation\Validator;
use Survey54\Reap\Framework\Exception\Error;

class PostRespondent extends RespondentController
{
    /**
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    public function execute(ServerRequestInterface $request): ResponseInterface
    {
        $params              = $this->validator->validate($request);
        $params['password']  = $request->getHeader('Authorization')[0] ?? null;
        $params['ipAddress'] = $request->getAttribute('ip_address');

        if (!$params['password']) {
            Error::throwError(Error::S54_PASSWORD_MISSING);
        }

        if (!preg_match(Validator::PASSWORD_REGEX, $params['password'])) {
            Error::throwError(Error::S54_PASSWORD_RULE_ERROR);
        }

        $message = $this->respondentService->create($params);

        return $this->response->withJson($message, 202);
    }
}
