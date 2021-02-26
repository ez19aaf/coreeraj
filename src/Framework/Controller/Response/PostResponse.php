<?php

namespace Survey54\Reap\Framework\Controller\Response;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class PostResponse extends ResponseController
{
    /**
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    public function execute(ServerRequestInterface $request): ResponseInterface
    {
        $params   = $this->validator->validate($request);
        $response = $this->responseService->create($params);
        return $this->response->withJson($response, 202);
    }
}
