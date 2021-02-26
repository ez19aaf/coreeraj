<?php

namespace Survey54\Reap\Framework\Controller\Response;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class PostResponseSms extends ResponseController
{
    /**
     * @param ServerRequestInterface $request
     */
    public function execute(ServerRequestInterface $request): ResponseInterface
    {
        $params   = $request->getParsedBody();
        $response = $this->responseService->smsResponse($params);
        return $this->response->withJson($response, 202);
    }
}
