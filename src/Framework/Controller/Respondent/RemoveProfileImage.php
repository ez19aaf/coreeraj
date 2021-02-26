<?php

namespace Survey54\Reap\Framework\Controller\Respondent;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class RemoveProfileImage extends RespondentController
{
    /**
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    public function execute(ServerRequestInterface $request): ResponseInterface
    {
        $userId = $request->getAttribute('uuid');
        $user   = $this->respondentService->removePhoto($userId);
        return $this->response->withJson($user, 202);
    }
}
