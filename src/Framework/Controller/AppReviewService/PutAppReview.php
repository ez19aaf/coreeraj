<?php

namespace Survey54\Reap\Framework\Controller\AppReviewService;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class PutAppReview extends AppReviewController
{
    /**
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    public function execute(ServerRequestInterface $request): ResponseInterface
    {
        $data         = $this->validator->validate($request);
        $data['uuid'] = $request->getAttribute('uuid');

        $appReview = $this->appReviewService->createOrUpdate($data);
        return $this->response->withJson($appReview, 200);
    }
}
