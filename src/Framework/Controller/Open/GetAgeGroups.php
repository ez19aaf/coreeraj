<?php

namespace Survey54\Reap\Framework\Controller\Open;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class GetAgeGroups extends OpenController
{
    /**
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    public function execute(ServerRequestInterface $request): ResponseInterface
    {
        $ageGroups = $this->openService->getAgeGroups();
        return $this->response->withJson($ageGroups, 200);
    }
}
