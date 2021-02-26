<?php

namespace Survey54\Reap\Framework\Controller\Open;

use Survey54\Library\Controller\Controller;
use Survey54\Library\Validation\ValidatorInterface;
use Survey54\Reap\Application\OpenService;

abstract class OpenController extends Controller
{
    protected OpenService $openService;

    /**
     * OpenController constructor.
     * @param OpenService $openService
     * @param ValidatorInterface|null $validator
     */
    public function __construct(OpenService $openService, ValidatorInterface $validator = null)
    {
        parent::__construct($validator);
        $this->openService = $openService;
    }
}
