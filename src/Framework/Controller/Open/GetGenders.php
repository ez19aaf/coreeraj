<?php

namespace Survey54\Reap\Framework\Controller\Open;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class GetGenders extends OpenController
{
    /**
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    public function execute(ServerRequestInterface $request): ResponseInterface
    {
        $genders = $this->openService->getGenders();
        return $this->response->withJson($genders, 200);
    }
}
