<?php


namespace Survey54\Reap\Framework\Controller\Group;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class DeleteGroup extends GroupController
{
    /**
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    public function execute(ServerRequestInterface $request): ResponseInterface
    {
        $data = $request->getParsedBody();
        $this->groupService->delete($data);
        return $this->response->withJson(true, 202);
    }
}
