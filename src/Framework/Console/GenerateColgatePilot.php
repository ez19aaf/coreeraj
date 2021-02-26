<?php

namespace Survey54\Reap\Framework\Console;

use Survey54\Library\Domain\Values\SurveyStatus;
use Survey54\Library\Domain\Values\TagsReserved;
use Survey54\Library\Utilities\DateTime;
use Survey54\Library\Utilities\Helper;
use Survey54\Library\Validation\QuestionValidator;
use Survey54\Reap\Application\Repository\SurveyRepository;
use Survey54\Reap\Domain\Survey;

class GenerateColgatePilot
{
    private SurveyRepository $surveyRepository;

    private const USER_ID   = '3acd0de6-9bb2-49bb-9c3f-bf438c98c445';
    private const SURVEY_ID = '3acd0de6-9bb2-49bb-9c3f-bf438c98c446';

    /**
     * GenerateColgatePilot constructor.
     * @param SurveyRepository $surveyRepository
     */
    public function __construct(SurveyRepository $surveyRepository)
    {
        $this->surveyRepository = $surveyRepository;
    }

    /**
     * Generate sample survey
     */
    public function execute(): void
    {
        $questions = Helper::decodeJsonFile(__DIR__ . '/json/colgate-questions.json');
        $questions = QuestionValidator::validateRaw($questions, true);

        $this->surveyRepository->add(Survey::build([
            'uuid'              => self::SURVEY_ID,
            'userId'            => self::USER_ID,
            'cintId'            => 101,
            'title'             => 'Sensodyne Brand research',
            'description'       => 'The purpose of this study is to understand why people buy the toothpaste brands they do',
            'expectedCompletes' => 120,
            'countries'         => [
                'South Africa',
            ],
            'questions'         => $questions,
            'image'             => 'https://res.cloudinary.com/survey54/image/upload/v1579996484/org/3acd0de6-9bb2-49bb-9c3f-bf438c98c445/sensodyne-toothpaste-mint-flavor.jpg',
            'tagLabels'         => [
                TagsReserved::BEAUTY_AND_PERSONAL_CARE,
                TagsReserved::HEALTH,
            ],
            'incentive'         => 20,
            'incentiveCurrency' => 'ZAR',
            'status'            => SurveyStatus::LAUNCHED,
            'createdAt'         => DateTime::generate(),
        ]));
    }
}
