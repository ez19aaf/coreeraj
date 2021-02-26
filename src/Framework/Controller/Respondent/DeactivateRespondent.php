<?php

namespace Survey54\Reap\Framework\Controller\Respondent;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Survey54\Library\Domain\Values\UserStatus;

class DeactivateRespondent extends RespondentController
{
    /**
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    public function execute(ServerRequestInterface $request): ResponseInterface
    {
        $uuid = $request->getAttribute('uuid');
        $user = $this->respondentService->changeStatus($uuid, UserStatus::DEACTIVATED);
        return $this->response->withJson($user, 202);
    }
}
