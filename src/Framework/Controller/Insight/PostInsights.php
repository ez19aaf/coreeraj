<?php

namespace Survey54\Reap\Framework\Controller\Insight;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class PostInsights extends InsightController
{
    /**
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    public function execute(ServerRequestInterface $request): ResponseInterface
    {
        $params = $this->validator->validate($request);

        $errors = $this->insightService->create($params);

        return $this->response->withJson($errors, 202);
    }
}
