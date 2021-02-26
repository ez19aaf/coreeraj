<?php

namespace Survey54\Reap\Framework\Controller\Response;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class PostGetAnalyticsDemographics extends ResponseController
{
    /**
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    public function execute(ServerRequestInterface $request): ResponseInterface
    {
        $params             = $this->validator->validate($request);
        $params['surveyId'] = $request->getAttribute('uuid');
        $list               = $this->responseService->analyticsDemographics($params);
        return $this->response->withJson($list, 200);
    }
}
