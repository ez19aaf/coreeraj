<?php

namespace Survey54\Reap\Framework\Controller\Survey;

use Survey54\Library\Controller\Controller;
use Survey54\Library\Validation\ValidatorInterface;
use Survey54\Reap\Application\SurveyService;

abstract class SurveyController extends Controller
{
    protected SurveyService $surveyService;

    /**
     * SurveyController constructor.
     * @param SurveyService $surveyService
     * @param ValidatorInterface|null $validator
     */
    public function __construct(SurveyService $surveyService, ValidatorInterface $validator = null)
    {
        parent::__construct($validator);
        $this->surveyService = $surveyService;
    }
}
