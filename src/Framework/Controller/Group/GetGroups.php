<?php


namespace Survey54\Reap\Framework\Controller\Group;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class GetGroups extends GroupController
{
    /**
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    public function execute(ServerRequestInterface $request): ResponseInterface
    {

        $this->validator->validate($request);

        $data = $this->buildBasicQueryParams($request);

        $groups = $this->groupService->getGroups($data);
        return $this->response->withJson($groups, 200);
    }
}
