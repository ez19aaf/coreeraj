<?php

namespace Survey54\Reap\Framework\Controller\Respondent;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class PutRespondent extends RespondentController
{
    /**
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    public function execute(ServerRequestInterface $request): ResponseInterface
    {
        $data         = $this->validator->validate($request);
        $data['uuid'] = $request->getAttribute('uuid');

        $user = $this->respondentService->updateDetails($data);
        return $this->response->withJson($user, 200);
    }
}
