<?php

namespace Survey54\Reap\Framework\Controller\Respondent;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class PutProfileImage extends RespondentController
{
    /**
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    public function execute(ServerRequestInterface $request): ResponseInterface
    {
        $data = $this->validator->validate($request);
        $uuid = $request->getAttribute('uuid');
        $user = $this->respondentService->uploadPhoto($uuid, $data);
        return $this->response->withJson($user, 202);
    }
}
