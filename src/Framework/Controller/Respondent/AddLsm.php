<?php

namespace Survey54\Reap\Framework\Controller\Respondent;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Survey54\Reap\Framework\Exception\Error;

class AddLsm extends RespondentController
{
    public function execute(ServerRequestInterface $request): ResponseInterface
    {
        $uuid = $request->getAttribute('uuid');
        if (($data = $request->getParsedBody()) === null || !is_array($data)) {
            Error::throwError(Error::S54_INVALID_JSON);
        }
        $result = $this->respondentService->addLsm($uuid, $data);
        return $this->response->withJson($result, 202);
    }
}
