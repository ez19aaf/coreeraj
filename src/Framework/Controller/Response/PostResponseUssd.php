<?php

namespace Survey54\Reap\Framework\Controller\Response;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class PostResponseUssd extends ResponseController
{
    /**
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    public function execute(ServerRequestInterface $request): ResponseInterface
    {
        $params   = $request->getParsedBody();
        $response = $this->responseService->ussdResponse($params);
        return $this->response->withHeader('Content-Type', 'text/plain')->write($response);
    }
}
