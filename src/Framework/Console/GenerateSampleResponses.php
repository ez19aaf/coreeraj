<?php

namespace Survey54\Reap\Framework\Console;

use Survey54\Library\Domain\Values\AgeGroup;
use Survey54\Library\Domain\Values\AuthStatus;
use Survey54\Library\Domain\Values\Country;
use Survey54\Library\Domain\Values\Employment;
use Survey54\Library\Domain\Values\Gender;
use Survey54\Library\Domain\Values\RespondentSurveyStatus;
use Survey54\Library\Domain\Values\SurveyStatus;
use Survey54\Library\Domain\Values\SurveyType;
use Survey54\Library\Domain\Values\TagsReserved;
use Survey54\Library\Domain\Values\UserStatus;
use Survey54\Library\Utilities\DateTime;
use Survey54\Library\Utilities\Helper;
use Survey54\Library\Utilities\UUID;
use Survey54\Library\Validation\QuestionValidator;
use Survey54\Reap\Application\Repository\InsightRepository;
use Survey54\Reap\Application\Repository\RespondentRepository;
use Survey54\Reap\Application\Repository\RespondentSurveyRepository;
use Survey54\Reap\Application\Repository\ResponseRepository;
use Survey54\Reap\Application\Repository\SurveyRepository;
use Survey54\Reap\Domain\Survey;

class GenerateSampleResponses
{
    private SurveyRepository $surveyRepository;
    private InsightRepository $insightRepository;
    private ResponseRepository $responseRepository;
    private RespondentRepository $respondentRepository;
    private RespondentSurveyRepository $respondentSurveyRepository;

    /**
     * GenerateSampleResponses constructor.
     * @param SurveyRepository $surveyRepository
     * @param InsightRepository $insightRepository
     * @param ResponseRepository $responseRepository
     * @param RespondentRepository $respondentRepository
     * @param RespondentSurveyRepository $respondentSurveyRepository
     */
    public function __construct(
        SurveyRepository $surveyRepository,
        InsightRepository $insightRepository,
        ResponseRepository $responseRepository,
        RespondentRepository $respondentRepository,
        RespondentSurveyRepository $respondentSurveyRepository
    ) {
        $this->surveyRepository           = $surveyRepository;
        $this->insightRepository          = $insightRepository;
        $this->responseRepository         = $responseRepository;
        $this->respondentRepository       = $respondentRepository;
        $this->respondentSurveyRepository = $respondentSurveyRepository;
    }

    /**
     * Generate sample responses
     */
    public function execute(): void
    {
        $ageGroupArr   = AgeGroup::toArray();
        $employmentArr = Employment::toArray();
        $genderArr     = Gender::toArray();

        $a4  = ['Tickets were too expensive.', 'I booked earlier and got affordable tickets.', 'My BA flight experience was good.', 'I got a cheap flight with 2 stop overs, very stressful experience, a longer journey, not recommended.'];
        $a11 = ['Better transport.', 'More tourist sites.', 'More hangout areas.', 'More cheap restaurants.'];
        $a12 = ['Visit more tourist locations.', 'Go for food tasting.', 'Clubbing.', 'Learn more about the culture.'];

        $responseBulk = $respondentBulk = $respondentSurveyBulk = [];

        $createdAt = DateTime::generate();

        //<editor-fold desc="Build Data">
        for ($i = 0; $i < 200; $i++) {
            $ageGroup   = $ageGroupArr[rand(0, 2)];
            $employment = $employmentArr[rand(0, 2)];
            $gender     = $genderArr[rand(0, 1)];

            $respondentId = UUID::generate();

            $respondentSurveyBulk[] = [
                'uuid'         => UUID::generate(),
                'respondentId' => $respondentId,
                'surveyId'     => SAMPLE_SURVEY_ID,
                'status'       => RespondentSurveyStatus::COMPLETED,
                'ipAddress'    => '',
                'createdAt'    => $createdAt,
            ];

            $mobile = "+23300000000$i";
            if ($i >= 10) {
                $mobile = "+2330000000$i";
                if ($i >= 100) {
                    $mobile = "+233000000$i";
                }
            }

            $respondentBulk[] = [
                'uuid'       => $respondentId,
                'email'      => "$respondentId@survey54.com",
                'mobile'     => $mobile,
                'ageGroup'   => $ageGroup,
                'employment' => $employment,
                'gender'     => $gender,
                'country'    => Country::GHANA,
                'isSample'   => 1,
                'userStatus' => UserStatus::ACTIVATED,
                'authStatus' => AuthStatus::VERIFIED,
                'createdAt'  => $createdAt,
            ];

            $responseBulk[] = [
                'uuid'         => UUID::generate(),
                'surveyId'     => SAMPLE_SURVEY_ID,
                'respondentId' => $respondentId,
                'questionId'   => 1,
                'answer'       => null,
                'answerIds'    => json_encode([rand(1, 2)], JSON_THROW_ON_ERROR, 512),
                'answerScale'  => null,
                'ageGroup'     => $ageGroup,
                'employment'   => $employment,
                'gender'       => $gender,
                'createdAt'    => DateTime::modify('+5 seconds'),
            ];
            $ans2           = rand(1, 4);
            $ans2ans        = ['Kenya', 'Nigeria', 'Australia'];
            $responseBulk[] = [
                'uuid'         => UUID::generate(),
                'surveyId'     => SAMPLE_SURVEY_ID,
                'respondentId' => $respondentId,
                'questionId'   => 2,
                'answer'       => $ans2 === 4 ? $ans2ans[rand(0, 2)] : null,
                'answerIds'    => json_encode([$ans2], JSON_THROW_ON_ERROR, 512),
                'answerScale'  => null,
                'ageGroup'     => $ageGroup,
                'employment'   => $employment,
                'gender'       => $gender,
                'createdAt'    => DateTime::modify('+10 seconds'),
            ];
            $ans3           = rand(1, 5);
            $ans3ans        = ['$2050', '$2010', '$2030'];
            $responseBulk[] = [
                'uuid'         => UUID::generate(),
                'surveyId'     => SAMPLE_SURVEY_ID,
                'respondentId' => $respondentId,
                'questionId'   => 3,
                'answer'       => $ans3 === 5 ? $ans3ans[rand(0, 2)] : null,
                'answerIds'    => json_encode([$ans3], JSON_THROW_ON_ERROR, 512),
                'answerScale'  => null,
                'ageGroup'     => $ageGroup,
                'employment'   => $employment,
                'gender'       => $gender,
                'createdAt'    => DateTime::modify('+15 seconds'),
            ];
            $responseBulk[] = [
                'uuid'         => UUID::generate(),
                'surveyId'     => SAMPLE_SURVEY_ID,
                'respondentId' => $respondentId,
                'questionId'   => 4,
                'answer'       => $a4[rand(0, 3)],
                'answerIds'    => json_encode(null, JSON_THROW_ON_ERROR, 512),
                'answerScale'  => null,
                'ageGroup'     => $ageGroup,
                'employment'   => $employment,
                'gender'       => $gender,
                'createdAt'    => DateTime::modify('+20 seconds'),
            ];
            $ans5           = rand(1, 5);
            $ans5ans        = ['Flat share', 'House share', 'Hostel'];
            $responseBulk[] = [
                'uuid'         => UUID::generate(),
                'surveyId'     => SAMPLE_SURVEY_ID,
                'respondentId' => $respondentId,
                'questionId'   => 5,
                'answer'       => $ans5 === 5 ? $ans5ans[rand(0, 2)] : null,
                'answerIds'    => json_encode([$ans5], JSON_THROW_ON_ERROR, 512),
                'answerScale'  => null,
                'ageGroup'     => $ageGroup,
                'employment'   => $employment,
                'gender'       => $gender,
                'createdAt'    => DateTime::modify('+25 seconds'),
            ];
            $responseBulk[] = [
                'uuid'         => UUID::generate(),
                'surveyId'     => SAMPLE_SURVEY_ID,
                'respondentId' => $respondentId,
                'questionId'   => 6,
                'answer'       => null,
                'answerIds'    => json_encode([rand(1, 5)], JSON_THROW_ON_ERROR, 512),
                'answerScale'  => null,
                'ageGroup'     => $ageGroup,
                'employment'   => $employment,
                'gender'       => $gender,
                'createdAt'    => DateTime::modify('+30 seconds'),
            ];
            $ans7           = rand(1, 4);
            $ans7ans        = ['Yes, but not  that hard', 'No, it was alright', 'No, but in some places'];
            $responseBulk[] = [
                'uuid'         => UUID::generate(),
                'surveyId'     => SAMPLE_SURVEY_ID,
                'respondentId' => $respondentId,
                'questionId'   => 7,
                'answer'       => $ans7 === 4 ? $ans7ans[rand(0, 2)] : null,
                'answerIds'    => json_encode([$ans7], JSON_THROW_ON_ERROR, 512),
                'answerScale'  => null,
                'ageGroup'     => $ageGroup,
                'employment'   => $employment,
                'gender'       => $gender,
                'createdAt'    => DateTime::modify('+35 seconds'),
            ];
            $ans8           = rand(1, 8);
            $ans8ans        = ['Church event', 'Food tasting', 'Games competition'];
            $responseBulk[] = [
                'uuid'         => UUID::generate(),
                'surveyId'     => SAMPLE_SURVEY_ID,
                'respondentId' => $respondentId,
                'questionId'   => 8,
                'answer'       => $ans8 === 8 ? $ans8ans[rand(0, 2)] : null,
                'answerIds'    => json_encode([$ans8], JSON_THROW_ON_ERROR, 512),
                'answerScale'  => null,
                'ageGroup'     => $ageGroup,
                'employment'   => $employment,
                'gender'       => $gender,
                'createdAt'    => DateTime::modify('+40 seconds'),
            ];
            $responseBulk[] = [
                'uuid'         => UUID::generate(),
                'surveyId'     => SAMPLE_SURVEY_ID,
                'respondentId' => $respondentId,
                'questionId'   => 9,
                'answer'       => null,
                'answerIds'    => json_encode([rand(1, 4)], JSON_THROW_ON_ERROR, 512),
                'answerScale'  => null,
                'ageGroup'     => $ageGroup,
                'employment'   => $employment,
                'gender'       => $gender,
                'createdAt'    => DateTime::modify('+45 seconds'),
            ];
            $ans10          = rand(1, 3);
            $ans10ans       = ['Yes, but not  that hard', 'No, it was alright', 'No, but in some places'];
            $responseBulk[] = [
                'uuid'         => UUID::generate(),
                'surveyId'     => SAMPLE_SURVEY_ID,
                'respondentId' => $respondentId,
                'questionId'   => 10,
                'answer'       => $ans10 === 3 ? $ans10ans[rand(0, 2)] : null,
                'answerIds'    => json_encode([$ans10], JSON_THROW_ON_ERROR, 512),
                'answerScale'  => null,
                'ageGroup'     => $ageGroup,
                'employment'   => $employment,
                'gender'       => $gender,
                'createdAt'    => DateTime::modify('+50 seconds'),
            ];
            $responseBulk[] = [
                'uuid'         => UUID::generate(),
                'surveyId'     => SAMPLE_SURVEY_ID,
                'respondentId' => $respondentId,
                'questionId'   => 11,
                'answer'       => $a11[rand(0, 3)],
                'answerIds'    => json_encode(null, JSON_THROW_ON_ERROR, 512),
                'answerScale'  => null,
                'ageGroup'     => $ageGroup,
                'employment'   => $employment,
                'gender'       => $gender,
                'createdAt'    => DateTime::modify('+55 seconds'),
            ];
            $responseBulk[] = [
                'uuid'         => UUID::generate(),
                'surveyId'     => SAMPLE_SURVEY_ID,
                'respondentId' => $respondentId,
                'questionId'   => 12,
                'answer'       => $a12[rand(0, 3)],
                'answerIds'    => json_encode(null, JSON_THROW_ON_ERROR, 512),
                'answerScale'  => null,
                'ageGroup'     => $ageGroup,
                'employment'   => $employment,
                'gender'       => $gender,
                'createdAt'    => DateTime::modify('+60 seconds'),
            ];
            $responseBulk[] = [
                'uuid'         => UUID::generate(),
                'surveyId'     => SAMPLE_SURVEY_ID,
                'respondentId' => $respondentId,
                'questionId'   => 13,
                'answer'       => null,
                'answerIds'    => json_encode([rand(1, 2)], JSON_THROW_ON_ERROR, 512),
                'answerScale'  => null,
                'ageGroup'     => $ageGroup,
                'employment'   => $employment,
                'gender'       => $gender,
                'createdAt'    => DateTime::modify('+65 seconds'),
            ];
            $ans14          = rand(1, 2);
            $ans14yes       = ['For a Church event', 'For more Food tasting', 'Cos it was fun last time'];
            $ans14no        = ['Too hot', 'Just fine for now', 'Travel is expensive'];
            $responseBulk[] = [
                'uuid'         => UUID::generate(),
                'surveyId'     => SAMPLE_SURVEY_ID,
                'respondentId' => $respondentId,
                'questionId'   => 14,
                'answer'       => $ans14 === 1 ? $ans14yes[rand(0, 2)] : $ans14no[rand(0, 2)],
                'answerIds'    => json_encode([$ans14], JSON_THROW_ON_ERROR, 512),
                'answerScale'  => null,
                'ageGroup'     => $ageGroup,
                'employment'   => $employment,
                'gender'       => $gender,
                'createdAt'    => DateTime::modify('+70 seconds'),
            ];
            $responseBulk[] = [
                'uuid'         => UUID::generate(),
                'surveyId'     => SAMPLE_SURVEY_ID,
                'respondentId' => $respondentId,
                'questionId'   => 15,
                'answer'       => null,
                'answerIds'    => json_encode(null, JSON_THROW_ON_ERROR, 512),
                'answerScale'  => rand(1, 10),
                'ageGroup'     => $ageGroup,
                'employment'   => $employment,
                'gender'       => $gender,
                'createdAt'    => DateTime::modify('+75 seconds'),
            ];
            echo '.';
        }
        //</editor-fold>

        $questions = Helper::decodeJsonFile(__DIR__ . '/json/sample-questions.json');
        $questions = QuestionValidator::validateRaw($questions, true);

        $this->surveyRepository->add(Survey::build([
            'uuid'                    => SAMPLE_SURVEY_ID,
            'userId'                  => SAMPLE_USER_ID,
            'title'                   => 'Sample: Ghana Tourism Survey',
            'description'             => 'Provide data on your recent visits to Ghana',
            'type'                    => SurveyType::WEB,
            'expectedCompletes'       => 200,
            'actualCompletes'         => 200,
            'actualCompletesPercent'  => 100,
            'countries'               => [Country::GHANA],
            'sample'                  => [
                'ageGroup'   => AgeGroup::toArray(),
                'employment' => Employment::toArray(),
                'gender'     => Gender::toArray(),
            ],
            'questions'               => $questions,
            'image'                   => 'https://res.cloudinary.com/survey54/image/upload/v1580001718/ghana_tourism_survey.jpg',
            'groupId'                 => null,
            'audience'                => null,
            'tagIds'                  => [
                'da3163e8-73a3-42d2-90e8-09d32bd52224',
                'da3163e8-73a3-42d2-90e8-09d32bd52227',
            ],
            'tagLabels'               => [
                TagsReserved::HOUSING,
                TagsReserved::TRAVEL,
            ],
            'favourite'               => true,
            'status'                  => SurveyStatus::COMPLETED,
            'orderId'                 => null,
            'countScreeningQuestions' => 0,
            'incidentRate'            => 0,
            'lengthOfInterview'       => 3,
            'incentive'               => 10,
            'incentiveCurrency'       => 'GHS',
            'smsCode'                 => 0,
            'ussdCode'                => 0,
            'category'                => null,
            'subject'                 => null,
            'recurrence'              => null,
            'pushNotification'        => false,
            'pushTo'                  => null,
        ]));

        [$A, $B] = array_chunk($respondentBulk, ceil(count($respondentBulk) / 2));
        $errors = $this->respondentRepository->addBulk($A);
        $this->errors($errors, 'Failed batch 1: save respondents');
        $errors = $this->respondentRepository->addBulk($B);
        $this->errors($errors, 'Failed batch 2: save respondents');

        [$C, $D] = array_chunk($respondentSurveyBulk, ceil(count($respondentSurveyBulk) / 2));
        $errors = $this->respondentSurveyRepository->addBulk($C);
        $this->errors($errors, 'Failed batch 1: save respondent surveys');
        $errors = $this->respondentSurveyRepository->addBulk($D);
        $this->errors($errors, 'Failed batch 1: save respondent surveys');

        [$E, $F, $G] = array_chunk($responseBulk, ceil(count($responseBulk) / 3));
        $errors = $this->responseRepository->addBulk($E);
        $this->errors($errors, 'Failed batch 1: save responses');
        $errors = $this->responseRepository->addBulk($F);
        $this->errors($errors, 'Failed batch 1: save responses');
        $errors = $this->responseRepository->addBulk($G);
        $this->errors($errors, 'Failed batch 1: save responses');

        $insightBulk = $this->buildInsights(SAMPLE_USER_ID, SAMPLE_SURVEY_ID, $createdAt);
        $errors      = $this->insightRepository->addBulk($insightBulk);
        $this->errors($errors, 'Failed batch 1: save insights');
    }

    /**
     * @param $errors
     * @param string $msg
     */
    private function errors($errors, string $msg): void
    {
        if ($errors !== null) {
            echo "\n$msg\n";
            print_r($errors);
            die;
        }
    }

    private function buildInsights(string $userId, string $surveyId, string $createdAt): array
    {
        $totalTFn = function () use ($surveyId): int {
            $search = [
                'surveyId'   => ['EQUALS', $surveyId],
                'questionId' => ['EQUALS', 1],
            ];
            return $this->responseRepository->count($search);
        };

        $employFn = function ($status) use ($surveyId): int {
            $search = [
                'surveyId'   => ['EQUALS', $surveyId],
                'questionId' => ['EQUALS', 1],
                'employment' => ['EQUALS', $status],
            ];
            return $this->responseRepository->count($search);
        };

        $questionFn = function (int $qid, int $aid) use ($surveyId): int {
            $search = [
                'surveyId'           => ['EQUALS', $surveyId],
                'questionId'         => ['EQUALS', $qid],
                "answerIds->>'$[0]'" => ['JSON', $aid],
            ];
            return $this->responseRepository->count($search);
        };

        $total   = $totalTFn();
        $percent = static function (int $num) use ($total) {
            return ($num / $total) * 100;
        };

        $uk = $questionFn(2, 1);
        $eu = $questionFn(2, 3);
        if ($uk > $eu) { // from Q2
            $summary1 = "More people traveled to Ghana in 2018 from UK ({$percent($uk)}%) than other parts of Europe ({$percent($eu)}%).";
        } else {
            $summary1 = "More people traveled to Ghana in 2018 from other parts of EU ({$percent($eu)}%) than UK ({$percent($uk)}%).";
        }

        $employed      = $employFn('Employed');
        $selfEmployed  = $employFn('Self-employed');
        $unemployed    = $employFn('Unemployed');
        $employedTotal = $employed + $selfEmployed;
        if ($unemployed > $employedTotal) {
            $summary2 = "People most likely traveled in search of job opportunity (as unemployed was highest at {$percent($unemployed)}%).";
        } else {
            $summary2 = "People most likely traveled for holiday or business trip rather than seeking job opportunity (as employed was highest at {$percent($employedTotal)}%).";
        }

        $accOptions    = [
            'Hotel'              => $questionFn(5, 1),
            'Airbnb'             => $questionFn(5, 2),
            'Service apartment'  => $questionFn(5, 3),
            'Friends and Family' => $questionFn(5, 4),
        ];
        $maxAcc        = max(array_values($accOptions));
        $accommodation = array_search($maxAcc, $accOptions, true);
        $summary3      = "People generally preferred to stay in $accommodation."; // from Q5

        $visitY = $questionFn(13, 1);
        $visitN = $questionFn(13, 2);
        if ($visitY > $visitN) { // from Q13
            $summary4 = "Many people enjoyed their stay in Ghana. {$percent($visitY)}% would visit again.";
        } else {
            $summary4 = "Not much people would revisit Ghana. Only {$percent($visitY)}% were intrigued.";
        }

        $tourY = $questionFn(7, 1);
        $tourN = $questionFn(7, 2);
        $tourI = $questionFn(7, 3);
        if ($tourY > $tourN) {
            $summary5 = "More people found it challenging to get around Ghana ({$percent($tourY)}%), while {$percent($tourI)}% thought the transportation needed improvement.";
        } else {
            $summary5 = "A few people found it challenging to get around Ghana ({$percent($tourY)}% vs {$percent($tourN)}%)";
        }

        $insightBulk = [
            [
                'uuid'      => UUID::generate(),
                'userId'    => $userId,
                'surveyId'  => $surveyId,
                'summary'   => $summary1,
                'createdAt' => $createdAt,
            ],
            [
                'uuid'      => UUID::generate(),
                'userId'    => $userId,
                'surveyId'  => $surveyId,
                'summary'   => $summary2,
                'createdAt' => $createdAt,
            ],
            [
                'uuid'      => UUID::generate(),
                'userId'    => $userId,
                'surveyId'  => $surveyId,
                'summary'   => $summary3,
                'createdAt' => $createdAt,
            ],
            [
                'uuid'      => UUID::generate(),
                'userId'    => $userId,
                'surveyId'  => $surveyId,
                'summary'   => $summary4,
                'createdAt' => $createdAt,
            ],
            [
                'uuid'      => UUID::generate(),
                'userId'    => $userId,
                'surveyId'  => $surveyId,
                'summary'   => $summary5,
                'createdAt' => $createdAt,
            ],
        ];
        return $insightBulk;
    }
}
