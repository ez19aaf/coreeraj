<?php

namespace Survey54\Reap\Framework\Console;

use Survey54\Library\Domain\Values\AgeGroup;
use Survey54\Library\Domain\Values\Country;
use Survey54\Library\Domain\Values\Employment;
use Survey54\Library\Domain\Values\Gender;
use Survey54\Library\Domain\Values\SurveyStatus;
use Survey54\Library\Domain\Values\SurveyType;
use Survey54\Library\Domain\Values\TagsReserved;
use Survey54\Library\Utilities\DateTime;
use Survey54\Library\Utilities\Helper;
use Survey54\Library\Validation\QuestionValidator;
use Survey54\Reap\Application\Repository\SurveyRepository;
use Survey54\Reap\Domain\Survey;

class GenerateAllQuestionFlows
{
    private SurveyRepository $surveyRepository;

    /**
     * GenerateAllQuestionFlows constructor.
     * @param SurveyRepository $surveyRepository
     */
    public function __construct(SurveyRepository $surveyRepository)
    {
        $this->surveyRepository = $surveyRepository;
    }

    /**
     * Generate survey with questions for all flows
     */
    public function execute(): void
    {
        $questions = Helper::decodeJsonFile(__DIR__ . '/json/all-questions-flows.json');
        $questions = QuestionValidator::validateRaw($questions, true);
        $sample    = [
            'ageGroup'   => AgeGroup::toArray(),
            'gender'     => Gender::toArray(),
            'employment' => Employment::toArray(),
        ];

        $this->surveyRepository->add(new Survey(
            '04f45fda-4954-45fd-ab1e-d831c5d148a1',
            '5b86feda-c0bc-49dd-885f-f52bdc1e6beb',
            101,
            'Testing: All Questions Survey',
            'Provide data for Product X',
            SurveyType::WEB,
            null,
            null,
            200,
            [Country::SOUTH_AFRICA],
            $sample,
            $questions,
            'https://images.unsplash.com/photo-1583254130193-563e6cb1fe44?ixlib=rb-1.2.1&ixid=eyJhcHBfaWQiOjEyMDd9&auto=format&fit=crop&w=350&q=80',
            [TagsReserved::BEAUTY_AND_PERSONAL_CARE, TagsReserved::FOOD_AND_DRINKS],
            0,
            10,
            'ZAR',
            SurveyStatus::LAUNCHED,
            null,
            DateTime::generate()
        ));
    }
}
