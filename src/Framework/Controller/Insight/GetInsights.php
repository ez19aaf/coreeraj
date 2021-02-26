<?php

namespace Survey54\Reap\Framework\Controller\Insight;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Survey54\Library\Controller\RestPagination;

class GetInsights extends InsightController
{
    /**
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    public function execute(ServerRequestInterface $request): ResponseInterface
    {
        $this->validator->validate($request);
        $data = $this->buildBasicQueryParams($request);

        $count   = $this->insightService->count($data);
        $results = $this->insightService->list($data) ?? [];

        $paginate        = new RestPagination($request, $count, $data['page'], $data['limit']);
        $paginatedResult = $paginate->buildPaginatedStructure($results);

        return $this->response->withJson($paginatedResult, 200);
    }
}
