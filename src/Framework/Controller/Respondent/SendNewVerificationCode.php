<?php

namespace Survey54\Reap\Framework\Controller\Respondent;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class SendNewVerificationCode extends RespondentController
{
    /**
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    public function execute(ServerRequestInterface $request): ResponseInterface
    {
        $params   = $this->validator->validate($request);
        $response = $this->respondentService->sendNewVerificationCode($params);
        return $this->response->withJson($response, 202);
    }
}
