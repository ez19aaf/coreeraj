<?php

namespace Survey54\Reap\Framework\Controller\Insight;

use Survey54\Library\Controller\Controller;
use Survey54\Library\Validation\ValidatorInterface;
use Survey54\Reap\Application\InsightService;

abstract class InsightController extends Controller
{
    protected InsightService $insightService;

    /**
     * InsightController constructor.
     * @param InsightService $insightService
     * @param ValidatorInterface|null $validator
     */
    public function __construct(InsightService $insightService, ValidatorInterface $validator = null)
    {
        parent::__construct($validator);
        $this->insightService = $insightService;
    }
}
