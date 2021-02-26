<?php

namespace Survey54\Reap\Framework\Controller\Group;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class PostGroup extends GroupController
{
    /**
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    public function execute(ServerRequestInterface $request): ResponseInterface
    {
        $params   = $this->validator->validate($request);
        $group = $this->groupService->create($params);
        return $this->response->withJson($group, 202);
    }
}
