<?php

namespace Survey54\Reap\Framework\Controller\Open;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class LsmCalculator extends OpenController
{
    public function execute(ServerRequestInterface $request): ResponseInterface
    {
        $lsmRecord = $this->openService->getLsmRecord();
        return $this->response->withJson($lsmRecord, 200);
    }
}
