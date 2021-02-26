<?php

namespace Survey54\Reap\Application;

use DateTime;
use Survey54\Library\Adapter\IpToCountryAdapter;
use Survey54\Library\Domain\Values\AgeGroup;
use Survey54\Library\Domain\Values\Country;
use Survey54\Library\Domain\Values\Employment;
use Survey54\Library\Domain\Values\Gender;
use Survey54\Library\Domain\Values\QuestionType;
use Survey54\Library\Domain\Values\Race;
use Survey54\Library\Domain\Values\RespondentSurveyStatus;
use Survey54\Library\Domain\Values\SurveyStatus;
use Survey54\Library\Domain\Values\SurveyType;
use Survey54\Library\Domain\Values\UserStatus;
use Survey54\Library\Domain\Values\UserType;
use Survey54\Library\Helper\AuthHelper;
use Survey54\Library\Helper\SearchBuilder;
use Survey54\Library\Message\TextMessageService;
use Survey54\Library\Utilities\UUID;
use Survey54\Reap\Application\Helper\DobHelper;
use Survey54\Reap\Application\Helper\SegmentationHelper;
use Survey54\Reap\Application\Repository\GhostRepository;
use Survey54\Reap\Application\Repository\RespondentRepository;
use Survey54\Reap\Application\Repository\RespondentSurveyRepository;
use Survey54\Reap\Application\Repository\ResponseRepository;
use Survey54\Reap\Application\Repository\SurveyRepository;
use Survey54\Reap\Domain\Objects\Question;
use Survey54\Reap\Domain\Respondent;
use Survey54\Reap\Domain\RespondentSurvey;
use Survey54\Reap\Domain\Response;
use Survey54\Reap\Domain\Survey;
use Survey54\Reap\Framework\Adapter\AfricaTalkingAdapter;
use Survey54\Reap\Framework\Exception\Error;

class ResponseService
{
    use AuthHelper;

    private GhostRepository $ghostRepository;
    private ResponseRepository $responseRepository;
    private RespondentRepository $respondentRepository;
    private RespondentSurveyRepository $respondentSurveyRepository;
    private SurveyRepository $surveyRepository;
    private RespondentService $respondentService;
    private TextMessageService $textMessageService;
    private AfricaTalkingAdapter $africaTalkingAdapter;
    private IpToCountryAdapter $ipToCountryAdapter;
    private string $secret;

    /**
     * ResponseService constructor.
     * @param GhostRepository $ghostRepository
     * @param ResponseRepository $responseRepository
     * @param RespondentRepository $respondentRepository
     * @param RespondentSurveyRepository $respondentSurveyRepository
     * @param SurveyRepository $surveyRepository
     * @param RespondentService $respondentService
     * @param TextMessageService $textMessageService
     * @param AfricaTalkingAdapter $africaTalkingAdapter
     * @param IpToCountryAdapter $ipToCountryAdapter
     * @param string $secret
     */
    public function __construct(
        GhostRepository $ghostRepository,
        ResponseRepository $responseRepository,
        RespondentRepository $respondentRepository,
        RespondentSurveyRepository $respondentSurveyRepository,
        SurveyRepository $surveyRepository,
        RespondentService $respondentService,
        TextMessageService $textMessageService,
        AfricaTalkingAdapter $africaTalkingAdapter,
        IpToCountryAdapter $ipToCountryAdapter,
        string $secret
    ) {
        $this->ghostRepository            = $ghostRepository;
        $this->responseRepository         = $responseRepository;
        $this->respondentRepository       = $respondentRepository;
        $this->respondentSurveyRepository = $respondentSurveyRepository;
        $this->surveyRepository           = $surveyRepository;
        $this->respondentService          = $respondentService;
        $this->textMessageService         = $textMessageService;
        $this->africaTalkingAdapter       = $africaTalkingAdapter;
        $this->ipToCountryAdapter         = $ipToCountryAdapter;
        $this->secret                     = $secret;
    }

    protected function getSecret(): string
    {
        return $this->secret;
    }

    /**
     * Callback used by Cint
     * @return array
     */
    public function completedCallback(): array
    {
        return [
            'message' => 'Respondent A has completed survey',
        ];
    }

    /**
     * @param string $identifier
     * @param string $surveyId
     * @param bool $isMobile
     * @return array
     */
    private function getLastResponse(string $identifier, string $surveyId, bool $isMobile = false): array
    {
        $search['surveyId'] = ['EQUALS', $surveyId];
        if ($isMobile) {
            $search["metadata->>'$.mobile'"] = ['JSON', $identifier];
        } else {
            $search['respondentId'] = ['EQUALS', $identifier];
        }
        return $this->responseRepository->list(0, 1, $search, ['-createdAt']); // descend by createdAt
    }

    /**
     * @param string $surveyId
     * @param array $respondentData
     * @param bool $excludeRespondentAdd
     */
    private function addRespondentToDB(string $surveyId, array $respondentData, bool $excludeRespondentAdd = false): void
    {
        // Create Respondent
        if (!$excludeRespondentAdd) {
            if ($respondentData['email'] && $this->respondentRepository->findByEmail($respondentData['email'])) {
                Error::throwError(Error::S542_EMAIL_ALREADY_EXIST);
            }
            $respondent = Respondent::build($respondentData);
            $ghost      = $this->ghostRepository->findByMobile($respondent->mobile);
            if (!$ghost) {
                $this->ipToCountryAdapter->validate($respondent->ipAddress, $respondent->country);
            }
            $this->respondentRepository->add($respondent);
        }

        // Add lsmGroup for Respondent
        if (!empty($respondentData['lsmKeys'])) {
            $this->respondentService->addLsm($respondentData['uuid'], $respondentData['lsmKeys']);
        }

        // Update missing demographics and demographicCompleted for Respondent
        /** @var Respondent $respondent */
        $respondent = $this->respondentRepository->find($respondentData['uuid']);

        if (DateTime::createFromFormat('d-m-Y', $respondent->dateOfBirth) !== false) {
            $respondent->dateOfBirth = $respondentData['dateOfBirth']; // just reset to old dateOfBirth
            $respondent->ageGroup    = DobHelper::getAgeGroupFromDOB($respondentData['dateOfBirth']);
        }
        if (!in_array($respondent->ageGroup, AgeGroup::toArray(), true)) {
            $respondent->ageGroup = $respondentData['ageGroup'];
        }
        if (!in_array($respondent->employment, Employment::toArray(), true)) {
            $respondent->employment = $respondentData['employment'];
        }
        if (!in_array($respondent->gender, Gender::toArray(), true)) {
            $respondent->gender = $respondentData['gender'];
        }
        if (($respondent->country === Country::SOUTH_AFRICA) && !in_array($respondent->race, Race::toArray(), true)) {
            $respondent->race = $respondentData['race'];
        }

        $respondent->demographicCompleted = SegmentationHelper::demographicCompletedCheck($respondent->toArray())['demographicCompleted'];

        $this->respondentRepository->update($respondent);

        // Create RespondentSurvey
        $this->respondentSurveyRepository->add(RespondentSurvey::build([
            'uuid'         => UUID::generate(),
            'respondentId' => $respondentData['uuid'],
            'surveyId'     => $surveyId,
            'status'       => RespondentSurveyStatus::STARTED,
            'ipAddress'    => $respondentData['ipAddress'] ?? '',
        ]));
    }

    /**
     * @param string $surveyId
     * @param int $questionId
     * @param Respondent|null $respondent
     * @param bool $open_ended_disabled
     * @return array|null
     */
    private function getQuestion(string $surveyId, int $questionId, ?Respondent $respondent = null, bool $open_ended_disabled = false): ?array
    {
        $question = $this->surveyRepository->getQuestionFindBySurveyAndQuestion($surveyId, $questionId);

        if ($open_ended_disabled && $question && $question['type'] === QuestionType::OPEN_ENDED && $question['goto'] === -1) {
            if ($respondent) {
                $data = [
                    'uuid'         => UUID::generate(),
                    'goto'         => $question['goto'] ?? null,
                    'respondentId' => $respondent->uuid,
                    'surveyId'     => $surveyId,
                    'questionId'   => $questionId,
                    'answer'       => 'This is a default USSD response.',
                    'ageGroup'     => $respondent->ageGroup,
                    'employment'   => $respondent->employment,
                    'gender'       => $respondent->gender,
                    'lsmGroup'     => $respondent->lsmGroup,
                    'race'         => $respondent->race,
                ];

                $response = Response::build($data);
                $this->responseRepository->add($response);
            }
            $questionId = $question['goto'] ? $question['goto'] - 1 : $questionId + 1;
            return $this->getQuestion($surveyId, $questionId, $respondent, $open_ended_disabled);
        }

        return $question ?? null;
    }

    /**
     * Response for SMS survey (and Audience Measurement survey)
     * @param array $data
     * @return array
     */
    public function smsResponse(array $data): array
    {
        $phoneNumber = $data['from'];
        $smsCode     = $data['to'];
        $text        = $data['text'];
        $smsData     = [
            'to'   => $phoneNumber,
            'from' => $smsCode,
        ];

        /** @var Survey $survey */
        if (!$survey = $this->surveyRepository->findBy(['smsCode' => ['EQUALS', $smsCode]])) {
            $smsData['error'] = 'Invalid service code.';

            return $smsData;
        }

        $respondent = $this->respondentRepository->findByMobile($phoneNumber);

        if ($respondent && $text && $responseSurvey = $this->respondentSurveyRepository->findByRespondentSurvey($respondent->uuid, $survey->uuid)) {
            if ($responseSurvey && $responseSurvey->status === RespondentSurveyStatus::COMPLETED) {
                $smsData['error'] = 'You have already completed this survey.';

                return $smsData;
            }

            $data = [
                'respondentId' => $respondent->uuid,
                'surveyId'     => $survey->uuid,
                'questionId'   => $responseSurvey->nextQuestionId ?? 1,
            ];

            $question = $this->surveyRepository->getQuestionFindBySurveyAndQuestion($data['surveyId'], $data['questionId'] - 1);

            $data['answer']      = null;
            $data['answerIds']   = null;
            $data['answerScale'] = null;

            switch ($question['type']) {
                case QuestionType::OPEN_ENDED:
                    $data['answer'] = $text;
                    break;
                case QuestionType::SINGLE_CHOICE:
                    $data['answerIds'] = [$text];
                    break;
                case QuestionType::MULTIPLE_CHOICE:
                    $data['answerIds'] = explode(',', $text);
                    break;
                case QuestionType::SCALE:
                    $data['answerScale'] = $text;
                    break;
                default:
                    $smsData['message'] = 'Invalid question type';

                    return $this->africaTalkingAdapter->sendSMS($smsData['from'], $smsData['message'], $smsData['to']);
            }

            $responseData = $this->create($data);
        } else {
            $data['respondentId']       = $respondent->uuid ?? null;
            $data['metadata']['mobile'] = $phoneNumber;
            $data['surveyId']           = $survey->uuid;

            $responseData = $this->setup($data);
        }

        if (!$responseData['isEndOfSurvey']) {
            $response = $responseData['nextQuestion']['text'];

            if ($responseData['nextQuestion']['options']) {
                $response .= '\n';
                foreach ($responseData['nextQuestion']['options'] as $options) {
                    $response .= $options['id'] . '. ' . $options['text'];
                    $response .= '\n';
                }
            }

            $smsData['message'] = $response;
        } else {
            $smsData['message'] = 'End of survey.';
        }

        return $this->africaTalkingAdapter->sendSMS($smsData['from'], $smsData['message'], $smsData['to']);
    }

    /**
     * Response for USSD survey
     * @param array $data
     * @return string
     */
    public function ussdResponse(array $data): string
    {
        $phoneNumber = $data['phoneNumber'];
        $text        = $data['text'] !== '' ? $data['text'] : false;

        if (!$survey = $this->surveyRepository->findBy(['ussdCode' => ['EQUALS', $data['serviceCode']]])) {
            return 'END Invalid service code.';
        }

        $respondent = $this->respondentRepository->findByMobile($phoneNumber);

        if ($respondent && $responseSurvey = $this->respondentSurveyRepository->findByRespondentSurvey($respondent->uuid, $survey->uuid)) {
            if ($responseSurvey && $responseSurvey->status === RespondentSurveyStatus::COMPLETED) {
                return 'END You have already completed this survey.';
            }
            $text = explode('*', $text);
            $text = $text[count($text) - 1];
            $data = [
                'respondentId'        => $respondent->uuid,
                'surveyId'            => $survey->uuid,
                'questionId'          => $responseSurvey->nextQuestionId ?? 1,
                'open_ended_disabled' => true,
            ];

            $question = $this->getQuestion($data['surveyId'], $data['questionId'] - 1, $respondent, true);

            $data['answer']      = null;
            $data['answerIds']   = null;
            $data['answerScale'] = null;

            switch ($question['type']) {
                case QuestionType::OPEN_ENDED:
                    $data['answer'] = $text;
                    break;
                case QuestionType::SINGLE_CHOICE:
                    $data['answerIds'] = [$text];
                    break;
                case QuestionType::MULTIPLE_CHOICE:
                    $data['answerIds'] = explode(',', $text);
                    break;
                case QuestionType::SCALE:
                    $data['answerScale'] = $text;
                    break;
                default:
                    return 'END Invalid question.';
            }

            $responseData = $this->create($data);
        } else {
            $data['respondentId']        = $respondent->uuid ?? null;
            $data['metadata']['mobile']  = $phoneNumber;
            $data['surveyId']            = $survey->uuid;
            $data['open_ended_disabled'] = true;

            $responseData = $this->setup($data);
        }

        if ($responseData['isEndOfSurvey']) {
            return 'END End of survey.';
        }

        $response = 'CON ' . $responseData['nextQuestion']['text'];

        if (!($responseData['nextQuestion']['options'] ?? false)) {
            return $response;
        }

        $response .= '\n';
        foreach ($responseData['nextQuestion']['options'] as $options) {
            $response .= $options['id'] . '. ' . $options['text'];
            $response .= '\n';
        }

        return $response;
    }

    /**
     * Sets up user for open survey (directly used by web and mobile), and responds with first question
     * @param array $data
     * @return array
     */
    public function setup(array $data): array
    {
        $metadata     = $data['metadata'];
        $email        = $metadata['email'] = $metadata['email'] ?? null;
        $mobile       = $metadata['mobile'];
        $respondentId = $metadata['uuid'] = $data['respondentId'];
        $surveyId     = $data['surveyId'];
        $questionId   = 0;

        /** @var $survey Survey */
        if (!$survey = $this->surveyRepository->find($surveyId)) {
            Error::throwError(Error::S542_LAUNCHED_SURVEY_NOT_FOUND);
        }
        if ($survey->status === SurveyStatus::COMPLETED) {
            Error::throwError(Error::S542_SURVEY_COMPLETED);
        }

        if ($respondentId && !$this->respondentRepository->find($respondentId)) {
            Error::throwError(Error::S542_RESPONDENT_ID_NOT_IN_DB);
        } else if ($respondent = $this->respondentRepository->findByMobile($mobile)) {
            $respondentId = $metadata['uuid'] = $respondent->uuid;
        }

        /** @var $rs RespondentSurvey */
        if ($respondentId && ($rs = $this->respondentSurveyRepository->findByRespondentSurvey($respondentId, $surveyId)) &&
            $rs->status === RespondentSurveyStatus::COMPLETED) {
            Error::throwError(Error::S542_YOU_HAVE_ALREADY_COMPLETED_THE_SURVEY);
        }

        if ($respondentId) {
            $response = $this->getLastResponse($respondentId, $surveyId);
        } else {
            $response = $this->getLastResponse($mobile, $surveyId, true);
        }

        // At this point $questionId=0, $respondentId is set

        if ($response) {
            $goto = $response[0]['goto'];
            // If goto is a non-zero number, set next Q to goto. Using $goto-1 because the DB search is performed on zero index.
            $questionId   = $goto !== null && $goto !== 0 ? $goto - 1 : $response[0]['questionId']; // NON_CINT: Returning user
            $respondentId = $response[0]['respondentId'];
        }

        if (!$respondentId) {
            $respondentId                        = $metadata['uuid'] = UUID::generate();   // NON_CINT: First call
            $metadata['convertedFromOpenSurvey'] = true;
            $this->addRespondentToDB($surveyId, $metadata);
        } else if (!$rs) {
            $this->addRespondentToDB($surveyId, $metadata, true);
        }

        /** @var Respondent $respondent */
        if (!$respondent = $this->respondentRepository->find($respondentId)) {
            $respondent = null;
        }

        $nextQuestion = $this->getQuestion($surveyId, $questionId, $respondent, $data['open_ended_disabled'] ?? false);
        if ($nextQuestion) {
            $this->respondentSurveyRepository->updateNextQuestionId($surveyId, $respondentId, $nextQuestion['id']);
        }

        $isEndOfSurvey  = !$nextQuestion;
        $isLastQuestion = $isEndOfSurvey || (is_array($nextQuestion) && $nextQuestion['id'] === count($survey->questions));
        $accessToken    = $this->generateAccessToken(UserType::RESPONDENT, $respondentId, $email ?? "$respondentId@survey54.com");

        return [
            'surveyId'       => $surveyId,
            'nextQuestion'   => $nextQuestion,
            'isEndOfSurvey'  => $isEndOfSurvey,
            'isLastQuestion' => $isLastQuestion,
            'respondentId'   => $respondentId,
            'accessToken'    => $accessToken,
        ];
    }

    /**
     * Adds a single response and responds with next question
     * @param array $data
     * @return array
     */
    public function create(array $data): array
    {
        /** @var $survey Survey */
        if (!$survey = $this->surveyRepository->find($data['surveyId'])) {
            Error::throwError(Error::S542_LAUNCHED_SURVEY_NOT_FOUND);
        }
        /** @var $respondent Respondent */
        if (!$respondent = $this->respondentRepository->find($data['respondentId'])) {
            Error::throwError(Error::S542_RESPONDENT_ID_NOT_IN_DB);
        }
        /** @var $rs RespondentSurvey */
        if (($rs = $this->respondentSurveyRepository->findByRespondentSurvey($data['respondentId'], $data['surveyId'])) &&
            $rs->status === RespondentSurveyStatus::COMPLETED) {
            Error::throwError(Error::S542_YOU_HAVE_ALREADY_COMPLETED_THE_SURVEY); // Don't proceed, to avoid adding incentive
        }

        // Validate goto
        $goto    = null;
        $gotoMap = [];

        // Add response for question if not exist
        if (!$this->responseRepository->findByRespondentSurveyQuestion($data['respondentId'], $data['surveyId'], $data['questionId'])) {
            // Validate answer fields
            $currentQId   = $data['questionId'] - 1; // zero indexed
            $currQuestion = $this->surveyRepository->getQuestionFindBySurveyAndQuestion($data['surveyId'], $currentQId);

            $a1 = $data['answer'] ?? null;
            $a2 = $data['answerIds'] ?? null;
            $a3 = $data['answerRank'] ?? null;
            $a4 = $data['answerScale'] ?? null;

            $withOther  = isset($currQuestion['withOther']) && $currQuestion['withOther'] === true;
            $withReason = isset($currQuestion['withReason']) && $currQuestion['withReason'] === true; // single choice only

            $options = $currQuestion['options'] ?? null;
            $a1null  = $a1 === null;

            switch ($currQuestion['type']) {
                case QuestionType::RANKING:
                    // answerIds must have all options and answerRank must have
                    if (!is_int($a3) || !is_array($a2) || count($a2) !== count($options) ||
                        !in_array($a3, array_column($options, 'id'), true)) {
                        Error::throwError(Error::S542_RANKING_QUESTION_REQUIREMENT);
                    }
                    $data['answer'] = $data['answerScale'] = null;
                    break;
                case QuestionType::SINGLE_CHOICE:
                    if ($withOther) {
                        if (!is_array($a2) || count($a2) !== 1) { // answer could be populated by other
                            Error::throwError(Error::S542_SINGLE_CHOICE_QUESTION_REQUIREMENT);
                        }
                    } else {
                        if ((!$withReason && !$a1null) || !is_array($a2) || count($a2) !== 1) {
                            Error::throwError(Error::S542_SINGLE_CHOICE_QUESTION_REQUIREMENT);
                        }
                        // goto with number is only used on single choice question's options
                        $key       = array_search($a2[0], array_column($options, 'id'), false);
                        $goto      = $options[$key]['goto']; // get the goto of the chosen answer
                        $gotoMap[] = $goto;
                    }
                    // withReason should append option to other
                    if ($withReason) {
                        // Get details of provided id in answerIds
                        $k = array_search($a2[0], array_column($options, 'id'), true);
                        // Append its text to answer
                        $data['answer'] = "{$options[$k]['text']}: $a1";
                    }
                    $data['answerRank'] = $data['answerScale'] = null;
                    break;
                case QuestionType::MULTIPLE_CHOICE:
                    if ($withOther) {
                        if (!is_array($a2) || count($a2) < 1) { // answer could be populated by other
                            Error::throwError(Error::S542_MULTIPLE_CHOICE_QUESTION_REQUIREMENT);
                        }
                    } else if (!$a1null || !is_array($a2) || count($a2) < 1) {
                        Error::throwError(Error::S542_MULTIPLE_CHOICE_QUESTION_REQUIREMENT);
                    }
                    // Get the goto of multiple answers
                    foreach ($a2 as $item) {
                        $key       = array_search($item, array_column($options, 'id'), false);
                        $gotoMap[] = $options[$key]['goto']; // get the goto of the chosen answer
                    }
                    $data['answerRank'] = $data['answerScale'] = null;
                    break;
                case QuestionType::OPEN_ENDED:
                    if (empty($a1)) {
                        Error::throwError(Error::S542_OPEN_ENDED_QUESTION_REQUIREMENT);
                    }
                    $data['answerIds'] = $data['answerRank'] = $data['answerScale'] = null;
                    break;
                case QuestionType::SCALE:
                    if (!is_int($a4) || $currQuestion['scale']['from'] > $a4 || $currQuestion['scale']['to'] < $a4) {
                        Error::throwError(Error::S542_SCALE_QUESTION_REQUIREMENT);
                    }
                    $data['answer'] = $data['answerIds'] = $data['answerScale'] = null;
                    break;
                default:
                    Error::throwError(Error::S542_UNSUPPORTED_QUESTION_TYPE);
            }

            $data['uuid']       = UUID::generate();
            $data['goto']       = $goto;
            $data['ageGroup']   = $respondent->ageGroup;
            $data['employment'] = $respondent->employment;
            $data['gender']     = $respondent->gender;
            $data['lsmGroup']   = $respondent->lsmGroup;
            $data['race']       = $respondent->race;
            $data['boundTime']  = $currQuestion['boundTime'] ?? null;
            $data['boundDate']  = $currQuestion['boundDate'] ?? null;

            $response = Response::build($data);
            $this->responseRepository->add($response);
        }

        // $gotoMap is used by AUD surveys
        if ($survey->type === SurveyType::AUD) {
            // Update gotoMap with recent and sort in ASC order
            $gotoMap = array_merge($rs->gotoMap ?? [], $gotoMap);
            sort($gotoMap);

            // Assign first element to goto
            $goto = $gotoMap[0];

            // Remove first element and save map
            array_shift($gotoMap);

            $rs          = $this->respondentSurveyRepository->find($rs->uuid);
            $rs->gotoMap = $gotoMap;
            $this->respondentSurveyRepository->update($rs);
        }

        /**
         * Goto Logic:
         * -1       End the survey
         * 2        Goes to the id of the question
         * null     Goes to the next question if any
         */
        if ($goto === -1) {
            $nextQuestion = null;
        } else if (is_int($goto) && $goto > 1) {
            $index        = $goto - 1;
            $nextQuestion = $this->getQuestion($survey->uuid, $index, $respondent, $data['open_ended_disabled'] ?? false);
        } else {
            $index        = $data['questionId']; // current questionId will be zero-index of next question
            $nextQuestion = $this->getQuestion($survey->uuid, $index, $respondent, $data['open_ended_disabled'] ?? false);
        }

        $isEndOfSurvey = !$nextQuestion;

        if ($isEndOfSurvey) {
            // End Survey for this respondent
            $this->respondentSurveyRepository->surveyCompleted($data['surveyId'], $data['respondentId']);
            // If this respondent's account is not activated: send them a message to activate
            $respondent = $this->respondentRepository->find($data['respondentId']);
            if ($respondent->userStatus !== UserStatus::ACTIVATED) {
                // Check Ghost
                $mobile = ($ghost = $this->ghostRepository->findByMobile($respondent->mobile)) ? $ghost->ghostMobile : $respondent->mobile;
                $this->textMessageService->sendActivationReminder($mobile);
            }

            // End Survey in general
            $search   = [
                'surveyId'       => ['EQUALS', $survey->uuid],
                'status'         => ['EQUALS', RespondentSurveyStatus::COMPLETED],
                'nextQuestionId' => ['EQUALS', count($survey->questions)], // used to make sure we only close the survey when we have respondents that answered all the questions: in case of surveys with qualifying questions
            ];
            $countRes = $this->respondentSurveyRepository->count($search);

            $survey = $this->surveyRepository->find($survey->uuid);

            if ($countRes >= $survey->expectedCompletes) {
                $survey->status                 = SurveyStatus::COMPLETED;
                $survey->actualCompletes        = $survey->expectedCompletes;
                $survey->actualCompletesPercent = 100;
            } else {
                $survey->status                 = SurveyStatus::LAUNCHED;
                $survey->actualCompletes        = $countRes;
                $survey->actualCompletesPercent = floor(($countRes / $survey->expectedCompletes) * 100);
            }

            $this->surveyRepository->update($survey);
        } else {
            $this->respondentSurveyRepository->updateNextQuestionId($survey->uuid, $data['respondentId'], $nextQuestion['id']);
        }

        $isLastQuestion = $isEndOfSurvey || (is_array($nextQuestion) && $nextQuestion['id'] === count($survey->questions));

        return [
            'surveyId'       => $survey->uuid,
            'nextQuestion'   => $nextQuestion,
            'isEndOfSurvey'  => $isEndOfSurvey,
            'isLastQuestion' => $isLastQuestion,
        ];
    }

    /**
     * @param array $data
     * @return array
     */
    public function analyticsDemographics(array $data): array
    {
        /** @var Survey $survey */
        $survey = $this->surveyRepository->find($data['surveyId']);
        if (!$survey) {
            Error::throwError(Error::S54_RESOURCE_NOT_FOUND);
        }
        if ($survey->countries[0] !== Country::SOUTH_AFRICA) {
            if (isset($data['lsmGroup'])) {
                Error::throwError(Error::S54_LSM_GROUP_DOES_NOT_APPLY);
            }
            if (isset($data['race'])) {
                Error::throwError(Error::S54_RACE_DOES_NOT_APPLY);
            }
        }
        if (!is_array($survey->questions) || count($survey->questions) < 1) {
            Error::throwError(Error::S54_QUESTIONS_NOT_FOUND);
        }

        $returnList   = [];
        $demographics = ['ageGroup', 'employment', 'gender'];

        if ($survey->countries[0] === Country::SOUTH_AFRICA) {
            $demographics[] = 'lsmGroup';
            $demographics[] = 'race';
        }

        // Populate $search with all items from $data
        $search = [];
        foreach ($data as $key => $value) {
            $search[$key] = ['IN', $value];
        }
        $search['surveyId'] = ['EQUALS', $data['surveyId']];

        $total = $this->responseRepository->count($search);

        foreach ($demographics as $demo) {
            $groupedList = $this->responseRepository->listGroupBy($demo, $search);
            $bag         = [];
            foreach ($groupedList as $item) {
                $bag[] = [
                    'key'   => $item[$demo],
                    'value' => round($item['count'] / $total * 100, 2),
                ];
            }
            $returnList[$demo] = $bag;
        }

        return $returnList;
    }

    /**
     * @param array $data
     * @return array
     */
    public function crosstabByResponse(array $data): array
    {
        /** @var Survey $survey */
        $survey = $this->surveyRepository->find($data['surveyId']);
        if (!$survey) {
            Error::throwError(Error::S54_RESOURCE_NOT_FOUND);
        }
        if (!is_array($survey->questions) || count($survey->questions) < 1) {
            Error::throwError(Error::S54_QUESTIONS_NOT_FOUND);
        }

        $compareQIDs = array_unique($data['compareQIDs']);
        sort($compareQIDs);

        // Populate $rSearch with all RESPONDENT IDS
        $sourceQID      = $data['source']['questionId'];
        $sourceResponse = $data['source']['response'];
        $sourceType     = '';
        $optionsCount   = 1;
        foreach ($survey->questions as $questionData) {
            $question = Question::build($questionData);
            if ($question->id === $sourceQID) {
                $sourceType   = $question->type;
                $optionsCount = count($question->options);
                break;
            }
        }

        if ($sourceType !== QuestionType::OPEN_ENDED && !is_int($sourceResponse)) {
            Error::throwError(Error::S54_RESPONSE_MUST_BE_NUMBER);
        }
        if ($sourceType !== QuestionType::OPEN_ENDED && $sourceResponse > $optionsCount) {
            Error::throwError(Error::S54_RESPONSE_MUST_BE_IN_OPTIONS);
        }

        $rSearch = [
            'questionId' => ['EQUALS', $sourceQID],
            'surveyId'   => ['EQUALS', $data['surveyId']],
        ];
        switch ($sourceType) {
            case QuestionType::SINGLE_CHOICE:
                $rSearch["answerIds->>'$[0]'"] = ['JSON', $sourceResponse];
                break;
            case QuestionType::MULTIPLE_CHOICE:
                $a = [];
                for ($i = 0; $i < $optionsCount; $i++) {
                    $a[] = "answerIds->>'$[$i]' = $sourceResponse";
                }
                $rSearch["answerIds->>'$[0]'"] = ['PLAIN', implode(' or ', $a)];
                break;
            case QuestionType::OPEN_ENDED:
                $rSearch['answer'] = ['LIKE', $sourceResponse];
                break;
            case QuestionType::RANKING:
                $rSearch['answerRank'] = ['EQUALS', $sourceResponse];
                break;
            case QuestionType::SCALE:
                $rSearch['answerScale'] = ['EQUALS', $sourceResponse];
                break;
        }

        $list = $this->responseRepository->list(0, 0, $rSearch, null, 'respondentId');
        $rIds = array_column($list, 'respondentId');

        $search = [
            'respondentId' => ['IN', $rIds],
        ];

        $returnList = [];

        foreach ($survey->questions as $questionData) {
            $question = Question::build($questionData);
            if (in_array($question->id, $compareQIDs, true)) {
                $returnList[$question->id] = $this->analyticsListSwitch($question, $search);
            }
            // Break loop if last target is reached
            if (end($compareQIDs) === $question->id) {
                break;
            }
        }

        return $returnList;
    }

    /**
     * @param array $data
     * @return array
     */
    public function crosstabByDemographics(array $data): array
    {
        /** @var Survey $survey */
        $survey = $this->surveyRepository->find($data['surveyId']);
        if (!$survey) {
            Error::throwError(Error::S54_RESOURCE_NOT_FOUND);
        }
        if (!is_array($survey->questions) || count($survey->questions) < 1) {
            Error::throwError(Error::S54_QUESTIONS_NOT_FOUND);
        }

        $compareQIDs = array_unique($data['compareQIDs']);
        sort($compareQIDs);

        // Populate $search with all DEMOGRAPHICS items from $data['source']
        $search = [];
        foreach ($data['source'] as $key => $value) {
            $search[$key] = ['IN', $value];
        }
        $search['surveyId'] = ['EQUALS', $data['surveyId']];

        $returnList = [];

        foreach ($survey->questions as $questionData) {
            $question = Question::build($questionData);
            if (in_array($question->id, $compareQIDs, true)) {
                $returnList[$question->id] = $this->analyticsListSwitch($question, $search);
            }
            // Break loop if last target is reached
            if (end($compareQIDs) === $question->id) {
                break;
            }
        }

        return $returnList;
    }

    /**
     * @param array $data
     * @return array
     */
    public function analyticsList(array $data): array
    {
        /** @var Survey $survey */
        $survey = $this->surveyRepository->find($data['surveyId']);
        if (!$survey) {
            Error::throwError(Error::S54_RESOURCE_NOT_FOUND);
        }
        if ($survey->countries[0] !== Country::SOUTH_AFRICA) {
            if (isset($data['lsmGroup'])) {
                Error::throwError(Error::S54_LSM_GROUP_DOES_NOT_APPLY);
            }
            if (isset($data['race'])) {
                Error::throwError(Error::S54_RACE_DOES_NOT_APPLY);
            }
        }
        if (!is_array($survey->questions) || count($survey->questions) < 1) {
            Error::throwError(Error::S54_QUESTIONS_NOT_FOUND);
        }

        // Populate $search with all DEMOGRAPHICS items from $data
        $search = [];
        foreach ($data as $key => $value) {
            $search[$key] = ['IN', $value];
        }
        $search['surveyId'] = ['EQUALS', $data['surveyId']];

        $returnList = [];

        foreach ($survey->questions as $questionData) {
            $question = Question::build($questionData);

            // Skip for specific question search
            if (isset($data['questionId']) && !in_array($question->id, $data['questionId'], true)) {
                continue;
            }

            $search['questionId'] = ['EQUALS', $question->id];

            $returnList[$question->id] = $this->analyticsListSwitch($question, $search);
        }

        return $returnList;
    }

    /**
     * @param Question $question
     * @param array $search
     * @return array
     */
    private function analyticsListSwitch(Question $question, array $search): array
    {
        $list  = $this->responseRepository->list(0, 0, $search);
        $total = count($list);

        switch ($question->type) {
            case QuestionType::OPEN_ENDED:
                $breakdown   = [];
                $groupedList = $this->responseRepository->listGroupBy('gender', $search);

                foreach ($groupedList as $item) {
                    $breakdown[] = [
                        'answer'     => $item['gender'],
                        'count'      => (int)$item['count'],
                        'percentage' => round($item['count'] / $total * 100, 2),
                    ];
                }

                // filter to remove empty strings
                $responses = array_values(array_filter(array_column($list, 'answer')));

                return [
                    'breakdown'    => $breakdown,
                    'responses'    => $responses, // The open_ended answers
                    'questionType' => $question->type,
                ];
            case QuestionType::SCALE:
                $breakdown   = [];
                $groupedList = $this->responseRepository->listGroupBy('answerScale', $search);

                foreach ($groupedList as $item) {
                    // Manually assign array index to scale
                    $breakdown[$item['answerScale']] = [
                        'answer'     => $item['answerScale'],
                        'count'      => (int)$item['count'],
                        'percentage' => round($item['count'] / $total * 100, 2),
                    ];
                }

                // Sort the indices manually assigned
                ksort($breakdown);

                return [
                    'breakdown'    => $breakdown,
                    'responses'    => [], // Any response that added other text.
                    'questionType' => $question->type,
                ];
            case QuestionType::SINGLE_CHOICE:
                $breakdown   = [];
                $groupedList = $this->responseRepository->listGroupBy('answerIds', $search);
                $options     = $question->options;

                foreach ($groupedList as $item) {
                    $answerId = json_decode($item['answerIds'], true, 512, JSON_THROW_ON_ERROR)[0];
                    $answer   = '';
                    foreach ($options as $option) {
                        if ($option['id'] === $answerId) {
                            $answer = $option['text'];
                            break;
                        }
                    }
                    $breakdown[] = [
                        'answer'     => $answer,
                        'count'      => (int)$item['count'],
                        'percentage' => round($item['count'] / $total * 100, 2),
                    ];
                }

                $responses = [];
                if ($question->withOther || $question->withReason) {
                    // filter to remove empty strings
                    $responses = array_values(array_filter(array_column($list, 'answer')));
                }

                // order by option
                $ordered = [];
                foreach ($options as $option) {
                    $arrSearch = array_search($option['text'], array_column($breakdown, 'answer'), true);
                    $ordered[] = $breakdown[$arrSearch];
                }

                return [
                    'breakdown'    => $ordered,
                    'responses'    => $responses, // Any response that added other text.
                    'questionType' => $question->type,
                ];
            case QuestionType::RANKING:
                $breakdown   = [];
                $groupedList = $this->responseRepository->listGroupBy('answerRank', $search);
                $options     = $question->options;

                foreach ($groupedList as $item) {
                    $answerRank = json_decode($item['answerRank'], true, 512, JSON_THROW_ON_ERROR)[0];
                    $answer     = '';
                    foreach ($options as $option) {
                        if ($option['id'] === $answerRank) {
                            $answer = $option['text'];
                            break;
                        }
                    }
                    $breakdown[] = [
                        'answer'     => $answer,
                        'count'      => (int)$item['count'],
                        'percentage' => round($item['count'] / $total * 100, 2),
                    ];
                }

                $responses = [];
                if ($question->withOther || $question->withReason) {
                    // filter to remove empty strings
                    $responses = array_values(array_filter(array_column($list, 'answer')));
                }

                // order by option
                $ordered = [];
                foreach ($options as $option) {
                    $arrSearch = array_search($option['text'], array_column($breakdown, 'answer'), true);
                    $ordered[] = $breakdown[$arrSearch];
                }

                return [
                    'breakdown'    => $ordered,
                    'responses'    => $responses, // Any response that added other text.
                    'questionType' => $question->type,
                ];
            case QuestionType::MULTIPLE_CHOICE:
                $breakdown   = [];
                $groupedList = $this->responseRepository->listGroupBy('answerIds', $search);
                $options     = $question->options;

                foreach ($groupedList as $item) {
                    $answerIds = json_decode($item['answerIds'], true, 512, JSON_THROW_ON_ERROR);
                    $answer    = '';
                    foreach ($options as $option) {
                        if (in_array($option['id'], $answerIds, true)) {
                            $answer = empty($answer) ? $option['text'] : $answer . ', ' . $option['text'];
                            break;
                        }
                    }
                    $breakdown[] = [
                        'answer'     => $answer,
                        'count'      => (int)$item['count'],
                        'percentage' => round($item['count'] / $total * 100, 2),
                    ];
                }

                $responses = [];
                if ($question->withOther || $question->withReason) {
                    // filter to remove empty strings
                    $responses = array_values(array_filter(array_column($list, 'answer')));
                }

                // order by option
                $ordered = [];
                foreach ($options as $option) {
                    $arrSearch = array_search($option['text'], array_column($breakdown, 'answer'), true);
                    $ordered[] = $breakdown[$arrSearch];
                }

                return [
                    'breakdown'    => $ordered,
                    'responses'    => $responses, // Any response that added other text.
                    'questionType' => $question->type,
                ];
        }
        return [];
    }

    /**
     * Used in FileService
     * @param array $data
     * @return mixed
     */
    public function list(array $data)
    {
        if (isset($data['surveyId'])) {

            /** @var $survey Survey */
            if (!$survey = $this->surveyRepository->find($data['surveyId'])) {
                Error::throwError(Error::S542_SURVEY_NOT_FOUND);
            }

            // For surveys with screening questions
            if ($survey->countScreeningQuestions > 0) {
                // Get respondents that qualified (all Qs answered) and COMPLETED the survey
                $search = [
                    'surveyId'       => ['EQUALS', $survey->uuid],
                    'status'         => ['EQUALS', RespondentSurveyStatus::COMPLETED],
                    'nextQuestionId' => ['EQUALS', count($survey->questions)], // TODO: record a field called lastScreeningQuestion
                ];
            } else {
                // Get respondents that COMPLETED the survey
                $search = [
                    'surveyId' => ['EQUALS', $survey->uuid],
                    'status'   => ['EQUALS', RespondentSurveyStatus::COMPLETED],
                ];
            }

            $res           = $this->respondentSurveyRepository->list(0, 0, $search, null, '`respondentId`');
            $respondentIds = array_column($res, 'respondentId');

            if (!isset($data['respondentId'])) {
                $data['respondentId'] = implode(',', $respondentIds);
            }
        }

        $builder = new SearchBuilder($data);
        $builder->addTerm('uuid', 'IN');
        $builder->addTerm('surveyId', 'EQUALS');
        $builder->addTerm('questionId', 'IN');
        $builder->addTerm('respondentId', 'IN');
        $builder->addTerm('answer', 'IN');
        $builder->addTerm('answerIds', 'JSON', $data['orRepeat']);
        $search = $builder->getSearch();

        return $this->responseRepository->list($data['offset'], $data['limit'], $search);
    }
}
