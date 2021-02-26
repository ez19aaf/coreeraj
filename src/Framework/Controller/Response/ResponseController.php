<?php

namespace Survey54\Reap\Framework\Controller\Response;

use Survey54\Library\Controller\Controller;
use Survey54\Library\Validation\ValidatorInterface;
use Survey54\Reap\Application\ResponseService;

abstract class ResponseController extends Controller
{
    protected ResponseService $responseService;

    /**
     * ResponseController constructor.
     * @param ResponseService $responseService
     * @param ValidatorInterface|null $validator
     */
    public function __construct(ResponseService $responseService, ValidatorInterface $validator = null)
    {
        parent::__construct($validator);
        $this->responseService = $responseService;
    }
}
