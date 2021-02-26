<?php


namespace Survey54\Reap\Framework\Console;

use Survey54\Library\Domain\Values\AgeGroup;
use Survey54\Library\Domain\Values\Country;
use Survey54\Library\Domain\Values\Employment;
use Survey54\Library\Domain\Values\Gender;
use Survey54\Library\Domain\Values\RespondentSurveyStatus;
use Survey54\Library\Domain\Values\SurveyStatus;
use Survey54\Library\Domain\Values\SurveyType;
use Survey54\Library\Utilities\DateTime;
use Survey54\Library\Utilities\Helper;
use Survey54\Library\Utilities\UUID;
use Survey54\Library\Validation\QuestionValidator;
use Survey54\Reap\Application\Repository\RespondentRepository;
use Survey54\Reap\Application\Repository\RespondentSurveyRepository;
use Survey54\Reap\Application\Repository\ResponseRepository;
use Survey54\Reap\Application\Repository\SurveyRepository;
use Survey54\Reap\Domain\Respondent;
use Survey54\Reap\Domain\Survey;

class GenerateAMResponses
{
    private SurveyRepository $surveyRepository;
    private ResponseRepository $responseRepository;
    private RespondentRepository $respondentRepository;
    private RespondentSurveyRepository $respondentSurveyRepository;

    /**
     * GenerateAMResponses constructor.
     * @param SurveyRepository $surveyRepository
     * @param ResponseRepository $responseRepository
     * @param RespondentRepository $respondentRepository
     * @param RespondentSurveyRepository $respondentSurveyRepository
     */
    public function __construct(
        SurveyRepository $surveyRepository,
        ResponseRepository $responseRepository,
        RespondentRepository $respondentRepository,
        RespondentSurveyRepository $respondentSurveyRepository
    ) {
        $this->surveyRepository           = $surveyRepository;
        $this->responseRepository         = $responseRepository;
        $this->respondentRepository       = $respondentRepository;
        $this->respondentSurveyRepository = $respondentSurveyRepository;
    }

    /**
     * Generate sample responses
     */
    public function execute(): void
    {
        // In this demo question 2 has only options 1 and 2

        $amSurveyId = '839334fc-99a5-4316-aaf6-1a9287fffbae';

        // use respondents from SAMPLE_SURVEY

        $search = [
            'surveyId' => ['EQUALS', SAMPLE_SURVEY_ID],
        ];
        $rIds   = $this->respondentSurveyRepository->list(0, 200, $search, null, '`respondentId`');
        $rIds   = array_column($rIds, 'respondentId');

        $search         = [
            'uuid' => ['IN', $rIds],
        ];
        $respondentList = $this->respondentRepository->list(0, 200, $search);

        $responseBulk = $respondentSurveyBulk = [];
        $createdAt    = DateTime::generate();
        $boundDate    = '28-07-2020';

        $stations = ['Adom FM', 'Peace FM', 'Radio Gold 90.5', 'Nhyira 104.5FM', 'Asempa 94.7FM', 'Joy 99.7FM']; // 6 stations

        foreach ($respondentList as $respondent) {
            $respondent   = Respondent::build($respondent);
            $respondentId = $respondent->uuid;
            $metadata     = [
                'ageGroup'   => $respondent->ageGroup,
                'employment' => $respondent->employment,
                'gender'     => $respondent->gender,
            ];

            $respondentSurveyBulk[] = [
                'uuid'           => UUID::generate(),
                'respondentId'   => $respondentId,
                'surveyId'       => $amSurveyId,
                'status'         => RespondentSurveyStatus::COMPLETED,
                'ipAddress'      => '',
                'nextQuestionId' => 20,
                'createdAt'      => $createdAt,
            ];

            $responseBulk[] = [
                'uuid'         => UUID::generate(),
                'surveyId'     => $amSurveyId,
                'respondentId' => $respondentId,
                'questionId'   => 1,
                'answer'       => null,
                'answerIds'    => json_encode([1], JSON_THROW_ON_ERROR, 512),
                'answerScale'  => null,
                'metadata'     => json_encode($metadata, JSON_THROW_ON_ERROR, 512),
                'ageGroup'     => $metadata['ageGroup'],
                'employment'   => $metadata['employment'],
                'gender'       => $metadata['gender'],
                'boundTime'    => null,
                'boundDate'    => $boundDate,
                'createdAt'    => DateTime::modify('+5 seconds'),
            ];

            $ans2 = (rand(1, 2) === 1) ? [rand(1, 2)] : [1, 2];
            //$ans2ans        = ['6-8AM', '8-10AM'];

            $responseBulk[] = [
                'uuid'         => UUID::generate(),
                'surveyId'     => $amSurveyId,
                'respondentId' => $respondentId,
                'questionId'   => 2,
                'answer'       => null,
                'answerIds'    => json_encode($ans2, JSON_THROW_ON_ERROR, 512),
                'answerScale'  => null,
                'metadata'     => json_encode($metadata, JSON_THROW_ON_ERROR, 512),
                'ageGroup'     => $metadata['ageGroup'],
                'employment'   => $metadata['employment'],
                'gender'       => $metadata['gender'],
                'boundTime'    => '6.00AM-10.00PM',
                'boundDate'    => $boundDate,
                'createdAt'    => DateTime::modify('+5 seconds'),
            ];

            if (in_array(1, $ans2, true)) {
                $ans3           = [rand(1, 8), rand(1, 8), rand(1, 8)];
                $options        = ['6.00AM-6.15AM', '6.15AM-6.30AM', '6.30AM-6.45AM', '6.45AM-7AM', '7.00AM-7.15AM', '7.15AM-7.30AM', '7.30AM-7.45AM', '7.45AM-8.00AM'];
                $responseBulk[] = [
                    'uuid'         => UUID::generate(),
                    'surveyId'     => $amSurveyId,
                    'respondentId' => $respondentId,
                    'questionId'   => 3,
                    'answer'       => null,
                    'answerIds'    => json_encode($ans3, JSON_THROW_ON_ERROR, 512),
                    'answerScale'  => null,
                    'metadata'     => json_encode($metadata, JSON_THROW_ON_ERROR, 512),
                    'ageGroup'     => $metadata['ageGroup'],
                    'employment'   => $metadata['employment'],
                    'gender'       => $metadata['gender'],
                    'boundTime'    => '6.00AM-8.00AM',
                    'boundDate'    => $boundDate,
                    'createdAt'    => DateTime::modify('+10 seconds'),
                ];

                for ($i = 1; $i <= 8; $i++) {
                    if (in_array($i, $ans3, true)) {
                        $t              = 15 + $i;
                        $responseBulk[] = [
                            'uuid'         => UUID::generate(),
                            'surveyId'     => $amSurveyId,
                            'respondentId' => $respondentId,
                            'questionId'   => 3 + $i,
                            'answer'       => $stations[rand(0, 5)],
                            'answerIds'    => null,
                            'answerScale'  => null,
                            'metadata'     => json_encode($metadata, JSON_THROW_ON_ERROR, 512),
                            'ageGroup'     => $metadata['ageGroup'],
                            'employment'   => $metadata['employment'],
                            'gender'       => $metadata['gender'],
                            'boundTime'    => $options[$i - 1],
                            'boundDate'    => $boundDate,
                            'createdAt'    => DateTime::modify("+$t seconds"),
                        ];
                    }
                }
            }


            if (in_array(2, $ans2, true)) {
                $ans12          = [rand(1, 8), rand(1, 8), rand(1, 8)];
                $options        = ['8.00AM-8.15AM', '8.15AM-8.30AM', '8.30AM-8.45AM', '8.45AM-9AM', '9.00AM-9.15AM', '9.15AM-9.30AM', '9.30AM-9.45AM', '9.45AM-10AM'];
                $responseBulk[] = [
                    'uuid'         => UUID::generate(),
                    'surveyId'     => $amSurveyId,
                    'respondentId' => $respondentId,
                    'questionId'   => 12,
                    'answer'       => null,
                    'answerIds'    => json_encode($ans12, JSON_THROW_ON_ERROR, 512),
                    'answerScale'  => null,
                    'metadata'     => json_encode($metadata, JSON_THROW_ON_ERROR, 512),
                    'ageGroup'     => $metadata['ageGroup'],
                    'employment'   => $metadata['employment'],
                    'gender'       => $metadata['gender'],
                    'boundTime'    => '8.00AM-10.00AM',
                    'boundDate'    => $boundDate,
                    'createdAt'    => DateTime::modify('+10 seconds'),
                ];


                for ($i = 1; $i <= 8; $i++) {
                    if (in_array($i, $ans12, true)) {
                        $t              = 15 + $i;
                        $responseBulk[] = [
                            'uuid'         => UUID::generate(),
                            'surveyId'     => $amSurveyId,
                            'respondentId' => $respondentId,
                            'questionId'   => 12 + $i,
                            'answer'       => $stations[rand(0, 5)],
                            'answerIds'    => null,
                            'answerScale'  => null,
                            'metadata'     => json_encode($metadata, JSON_THROW_ON_ERROR, 512),
                            'ageGroup'     => $metadata['ageGroup'],
                            'employment'   => $metadata['employment'],
                            'gender'       => $metadata['gender'],
                            'boundTime'    => $options[$i - 1],
                            'boundDate'    => $boundDate,
                            'createdAt'    => DateTime::modify("+$t seconds"),
                        ];
                    }
                }
            }
        }

        $questions = Helper::decodeJsonFile(__DIR__ . '/json/am-sample-questions.json');
        $questions = QuestionValidator::validateAM($questions, true);

        $this->surveyRepository->add(Survey::build([
            'uuid'                   => $amSurveyId,
            'userId'                 => SAMPLE_USER_ID,
            'title'                  => 'Sample: Audience Measurement Survey',
            'description'            => 'Provide blah',
            'type'                   => SurveyType::AUD,
            'expectedCompletes'      => 200,
            'actualCompletes'        => 200,
            'actualCompletesPercent' => 100,
            'sample'                 => [
                'ageGroup'   => AgeGroup::toArray(),
                'gender'     => Gender::toArray(),
                'employment' => Employment::toArray(),
            ],
            'countries'              => [Country::GHANA],
            'questions'              => $questions,
            'image'                  => 'https://res.cloudinary.com/survey54/image/upload/v1580001718/ghana_tourism_survey.jpg',
            'tagIds'                 => [
                'da3163e8-73a3-42d2-90e8-09d32bd52224',
                'da3163e8-73a3-42d2-90e8-09d32bd52227',
            ],
            'incentive'              => 10,
            'incentiveCurrency'      => 'GHS',
            'status'                 => SurveyStatus::COMPLETED,
            'cintId'                 => 101,
        ]));


        [$C, $D] = array_chunk($respondentSurveyBulk, ceil(count($respondentSurveyBulk) / 2));
        $this->respondentSurveyRepository->addBulk($C);
        $this->respondentSurveyRepository->addBulk($D);

        [$E, $F, $G] = array_chunk($responseBulk, ceil(count($responseBulk) / 3));
        $this->responseRepository->addBulk($E);
        $this->responseRepository->addBulk($F);
        $this->responseRepository->addBulk($G);
    }
}
