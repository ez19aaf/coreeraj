<?php

namespace Survey54\Reap\Framework\Controller\Ghost;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Survey54\Library\Controller\RestPagination;

class GetGhosts extends GhostController
{
    /**
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    public function execute(ServerRequestInterface $request): ResponseInterface
    {
        $this->validator->validate($request);
        $data = $this->buildBasicQueryParams($request);

        $count   = $this->ghostService->count($data);
        $results = $this->ghostService->list($data) ?? [];

        $paginate        = new RestPagination($request, $count, $data['page'], $data['limit']);
        $paginatedResult = $paginate->buildPaginatedStructure($results);

        return $this->response->withJson($paginatedResult, 200);
    }
}
