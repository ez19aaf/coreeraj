<?php

namespace Survey54\Reap\Framework\Controller\Ghost;

use Survey54\Library\Controller\Controller;
use Survey54\Library\Validation\ValidatorInterface;
use Survey54\Reap\Application\GhostService;

abstract class GhostController extends Controller
{
    protected GhostService $ghostService;

    /**
     * GhostController constructor.
     * @param GhostService $ghostService
     * @param ValidatorInterface|null $validator
     */
    public function __construct(GhostService $ghostService, ValidatorInterface $validator = null)
    {
        parent::__construct($validator);
        $this->ghostService = $ghostService;
    }
}
