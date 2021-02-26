<?php

namespace Survey54\Reap\Framework\Controller\Respondent;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class ChangePassword extends RespondentController
{
    /**
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    public function execute(ServerRequestInterface $request): ResponseInterface
    {
        $params         = $this->validator->validate($request);
        $params['uuid'] = strtolower($request->getAttribute('uuid'));

        $user = $this->respondentService->changePassword($params);

        return $this->response->withJson($user, 202);
    }
}
