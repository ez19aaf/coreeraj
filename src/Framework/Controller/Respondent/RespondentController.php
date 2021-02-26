<?php

namespace Survey54\Reap\Framework\Controller\Respondent;

use Survey54\Library\Controller\Controller;
use Survey54\Library\Validation\ValidatorInterface;
use Survey54\Reap\Application\RespondentService;

abstract class RespondentController extends Controller
{
    protected RespondentService $respondentService;

    /**
     * RespondentController constructor.
     * @param RespondentService $respondentService
     * @param ValidatorInterface|null $validator
     */
    public function __construct(RespondentService $respondentService, ValidatorInterface $validator = null)
    {
        parent::__construct($validator);
        $this->respondentService = $respondentService;
    }
}
