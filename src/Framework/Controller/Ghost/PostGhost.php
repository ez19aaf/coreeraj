<?php

namespace Survey54\Reap\Framework\Controller\Ghost;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class PostGhost extends GhostController
{
    /**
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    public function execute(ServerRequestInterface $request): ResponseInterface
    {
        $params = $this->validator->validate($request);
        $result = $this->ghostService->create($params);
        return $this->response->withJson($result, 202);
    }
}
