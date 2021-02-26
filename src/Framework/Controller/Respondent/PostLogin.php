<?php

namespace Survey54\Reap\Framework\Controller\Respondent;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Survey54\Reap\Framework\Exception\Error;

class PostLogin extends RespondentController
{
    /**
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    public function execute(ServerRequestInterface $request): ResponseInterface
    {
        $params             = $this->validator->validate($request);
        $params['password'] = $request->getHeader('Authorization')[0] ?? null;
        if (!$params['password']) {
            Error::throwError(Error::S54_PASSWORD_MISSING);
        }
        $response = $this->respondentService->login($params);
        return $this->response->withJson($response, 202);
    }
}
