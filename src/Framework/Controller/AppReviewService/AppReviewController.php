<?php

namespace Survey54\Reap\Framework\Controller\AppReviewService;

use Survey54\Library\Controller\Controller;
use Survey54\Library\Validation\ValidatorInterface;
use Survey54\Reap\Application\AppReviewService;

abstract class AppReviewController extends Controller
{
    protected AppReviewService $appReviewService;

    /**
     * AppReviewController constructor.
     * @param AppReviewService $appReviewService
     * @param ValidatorInterface|null $validator
     */
    public function __construct(AppReviewService $appReviewService, ValidatorInterface $validator = null)
    {
        parent::__construct($validator);
        $this->appReviewService = $appReviewService;
    }
}
