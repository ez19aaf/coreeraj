<?php

namespace Survey54\Reap\Framework\Controller\Open;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class GetRaces extends OpenController
{
    /**
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    public function execute(ServerRequestInterface $request): ResponseInterface
    {
        $races = $this->openService->getRaces();
        return $this->response->withJson($races, 200);
    }
}
