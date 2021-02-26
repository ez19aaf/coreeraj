<?php


namespace Survey54\Reap\Framework\Controller\Group;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class PutGroup extends GroupController
{
    /**
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    public function execute(ServerRequestInterface $request): ResponseInterface
    {
        $data           = $this->validator->validate($request);
        $data['uuid']   = $request->getAttribute('uuid');
        $message        = $this->groupService->update($data);
        return $this->response->withJson($message, 200);
    }
}
