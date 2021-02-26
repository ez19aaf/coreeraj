<?php

namespace Survey54\Reap\Framework\Controller\Log;

use Survey54\Library\Controller\Controller;
use Survey54\Library\Validation\ValidatorInterface;
use Survey54\Reap\Application\LogService;

abstract class LogController extends Controller
{
    protected LogService $logService;

    /**
     * LogController constructor.
     * @param LogService $logService
     * @param ValidatorInterface|null $validator
     */
    public function __construct(LogService $logService, ValidatorInterface $validator = null)
    {
        parent::__construct($validator);
        $this->logService = $logService;
    }
}
