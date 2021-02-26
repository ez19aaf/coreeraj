<?php

namespace Survey54\Reap\Framework\Controller\Respondent;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class ForgotPassword extends RespondentController
{
    /**
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    public function execute(ServerRequestInterface $request): ResponseInterface
    {
        $params = $this->validator->validate($request);
        $this->respondentService->forgotPassword($params);
        return $this->response->withStatus(202);
    }
}
