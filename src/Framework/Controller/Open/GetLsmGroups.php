<?php

namespace Survey54\Reap\Framework\Controller\Open;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class GetLsmGroups extends OpenController
{
    /**
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    public function execute(ServerRequestInterface $request): ResponseInterface
    {
        $lsmGroups = $this->openService->getLsmGroups();
        return $this->response->withJson($lsmGroups, 200);
    }
}
