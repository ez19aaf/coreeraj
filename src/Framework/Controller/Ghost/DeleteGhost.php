<?php

namespace Survey54\Reap\Framework\Controller\Ghost;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class DeleteGhost extends GhostController
{
    /**
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    public function execute(ServerRequestInterface $request): ResponseInterface
    {
        $uuid = $request->getAttribute('uuid');
        $this->ghostService->delete($uuid);
        return $this->response->withStatus(200);
    }
}
