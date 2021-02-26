<?php

namespace Survey54\Reap\Application;

use Survey54\Library\Domain\Values\Country;
use Survey54\Library\Domain\Values\RespondentSurveyStatus;
use Survey54\Library\Domain\Values\SurveyPushTo;
use Survey54\Library\Domain\Values\SurveyStatus;
use Survey54\Library\Domain\Values\SurveyType;
use Survey54\Library\Domain\Values\TagsReserved;
use Survey54\Library\Exception\ExtendedException;
use Survey54\Library\Helper\SearchBuilder;
use Survey54\Library\Message\MessageService;
use Survey54\Library\Message\TextMessageService;
use Survey54\Library\Utilities\UUID;
use Survey54\Reap\Application\Helper\DobHelper;
use Survey54\Reap\Application\Helper\SegmentationHelper;
use Survey54\Reap\Application\Repository\AirtimeCsvRepository;
use Survey54\Reap\Application\Repository\GhostRepository;
use Survey54\Reap\Application\Repository\LogRepository;
use Survey54\Reap\Application\Repository\RespondentRepository;
use Survey54\Reap\Application\Repository\RespondentSurveyRepository;
use Survey54\Reap\Application\Repository\SurveyRepository;
use Survey54\Reap\Domain\AirtimeLogsCsv;
use Survey54\Reap\Domain\Log;
use Survey54\Reap\Domain\Respondent;
use Survey54\Reap\Domain\Survey;
use Survey54\Reap\Framework\Adapter\AfricaTalkingAdapter;
use Survey54\Reap\Framework\Adapter\AirtimeAdapter;
use Survey54\Reap\Framework\Exception\Error;

class SurveyService
{
    private AirtimeCsvRepository $airtimeCsvRepository;
    private GhostRepository $ghostRepository;
    private LogRepository $logRepository;
    private RespondentRepository $respondentRepository;
    private RespondentSurveyRepository $respondentSurveyRepository;
    private SurveyRepository $surveyRepository;
    private MessageService $messageService;
    private RespondentService $respondentService;
    private TextMessageService $textMessageService;
    private AfricaTalkingAdapter $africasTalkingAdapter;
    private AirtimeAdapter $airtimeAdapter;

    /**
     * SurveyService constructor.
     * @param AirtimeCsvRepository $airtimeCsvRepository
     * @param GhostRepository $ghostRepository
     * @param LogRepository $logRepository
     * @param RespondentRepository $respondentRepository
     * @param RespondentSurveyRepository $respondentSurveyRepository
     * @param SurveyRepository $surveyRepository
     * @param MessageService $messageService
     * @param RespondentService $respondentService
     * @param TextMessageService $textMessageService
     * @param AfricaTalkingAdapter $africasTalkingAdapter
     * @param AirtimeAdapter $airtimeAdapter
     */
    public function __construct(
        AirtimeCsvRepository $airtimeCsvRepository,
        GhostRepository $ghostRepository,
        LogRepository $logRepository,
        RespondentRepository $respondentRepository,
        RespondentSurveyRepository $respondentSurveyRepository,
        SurveyRepository $surveyRepository,
        MessageService $messageService,
        RespondentService $respondentService,
        TextMessageService $textMessageService,
        AfricaTalkingAdapter $africasTalkingAdapter,
        AirtimeAdapter $airtimeAdapter
    ) {
        $this->airtimeCsvRepository       = $airtimeCsvRepository;
        $this->ghostRepository            = $ghostRepository;
        $this->logRepository              = $logRepository;
        $this->respondentRepository       = $respondentRepository;
        $this->respondentSurveyRepository = $respondentSurveyRepository;
        $this->surveyRepository           = $surveyRepository;
        $this->messageService             = $messageService;
        $this->respondentService          = $respondentService;
        $this->textMessageService         = $textMessageService;
        $this->africasTalkingAdapter      = $africasTalkingAdapter;
        $this->airtimeAdapter             = $airtimeAdapter;
    }

    /**
     * For now limits to surveys with expectedCompletes <= 1000
     * @param string $surveyId
     */
    public function sendNotification(string $surveyId): void
    {
        /** @var Survey $survey */
        if (!$survey = $this->surveyRepository->find($surveyId)) {
            Error::throwError(Error::S542_SURVEY_NOT_FOUND);
        }

        if ($survey->status !== SurveyStatus::LAUNCHED) {
            Error::throwError(Error::S542_LAUNCHED_SURVEY_EXPECTED);
        }

        if ($survey->expectedCompletes > 1000) {
            return; // For now limit notification to 1000
        }

        $survey->pushNotification = true;
        $this->surveyRepository->update($survey);

        // Segment by country and demographics
        $search = [
            'country'    => ['EQUALS', $survey->countries[0]],
            'ageGroup'   => ['IN', $survey->sample['ageGroup']],
            'employment' => ['IN', $survey->sample['employment']],
            'gender'     => ['IN', $survey->sample['gender']],
        ];

        // Segment for SA
        if ($survey->countries[0] === Country::SOUTH_AFRICA) {
            $search['lsmGroup'] = ['IN', $survey->sample['lsmGroup']];
            $search['race']     = ['IN', $survey->sample['race']];
        }

        // Exclude respondents that have already completed the survey
        $rsSearch      = [
            'surveyId' => ['EQUALS', $survey->uuid],
            'status'   => ['EQUALS', RespondentSurveyStatus::COMPLETED],
        ];
        $respondentIds = $this->respondentSurveyRepository->list(0, 0, $rsSearch, null, 'respondentId');
        if (count($respondentIds) > 0) {
            $respondentIds  = array_column($respondentIds, 'respondentId');
            $search['uuid'] = ['NOT_IN', $respondentIds];
        }

        // => PUSH TO GHOST
        if ($survey->pushTo === SurveyPushTo::GHOST) {
            // Get the ghost mobiles for the org of the survey
            $ghostMobiles = $this->ghostRepository->listGhostMobileByOrganisationId($survey->userId);
            if (!$ghostMobiles) {
                return;
            }

            // Add the retrieved ghost mobile to the search
            $search['isGhost']     = ['EQUALS', 1];
            $search['ghostMobile'] = ['IN', $ghostMobiles];

            $respGhostMobiles = $this->respondentRepository->list(0, 0, $search, null, 'ghostMobile');
            if (!$respGhostMobiles) {
                return;
            }

            $numbers  = array_column($respGhostMobiles, 'ghostMobile');
            $response = $this->textMessageService->pushSurvey($numbers, $survey->title, $survey->uuid, $survey->incentive, $survey->incentiveCurrency);
            $request  = [
                'count'   => count($numbers),
                'numbers' => $numbers,
            ];
            $log      = new Log(UUID::generate(), $survey->uuid, 'Survey', 'PushSurvey', $request, $response);
            $this->logRepository->add($log);
            return;
        }

        // => EMAIL TO LIVE
        // Check that email is not already sent for this survey
        $emailSent = $this->logRepository->findBy([
            'objectId'   => ['EQUALS', $survey->uuid],
            'objectType' => ['EQUALS', 'Survey'],
            'action'     => ['EQUALS', 'EmailSurvey'],
        ]);

        if (!$emailSent) {
            // Get the emails of the search
            $search['email'] = ['NOT_NULL', '_'];
            $emails          = $this->respondentRepository->list(0, 0, $search, null, 'email');
            $emails          = array_column($emails, 'email');
            // Send the email
            $loi      = $survey->lengthOfInterview > 0 ? $survey->lengthOfInterview : 2;
            $response = $this->messageService->sendRespondentNewSurvey(
                $emails,
                $survey->incentive,
                $survey->incentiveCurrency,
                $surveyId,
                $survey->expectedCompletes,
                $survey->tagLabels[0],
                $loi
            );
            $response = [
                'successCount' => $response,
            ];
            $request  = [
                'count'  => count($emails),
                'emails' => $emails,
            ];
            // Log to show that email is now sent for this survey
            $log = new Log(UUID::generate(), $survey->uuid, 'Survey', 'EmailSurvey', $request, $response);
            $this->logRepository->add($log);
        }

        // => PUSH TO LIVE
        // Get all mobiles already notified
        $sentMobiles = $this->logRepository->findBy([
            'objectId'   => ['EQUALS', $survey->uuid],
            'objectType' => ['EQUALS', 'Survey'],
            'action'     => ['EQUALS', 'PushSurveyAll'],
        ]);

        // Get mobile of all respondents of the search
        $mobiles = $this->respondentRepository->list(0, 0, $search, null, 'mobile');
        $mobiles = array_column($mobiles, 'mobile');
        if (!$mobiles) {
            return;
        }

        // Randomly select
        // If expectedCompletes is <=500 send in chunks of 100
        $chunk     = $survey->expectedCompletes > 500 ? 200 : 100;
        $remaining = $survey->expectedCompletes - $survey->actualCompletes;
        // Use remaining completes if less than chunk
        $chunk       = $remaining < $chunk ? $remaining : $chunk;
        $mobilesRand = array_rand($mobiles, $chunk);
    }

    /**
     * For launching WEB type surveys
     * @param string $surveyId
     */
    public function launchWebSurvey(string $surveyId): void
    {
        /** @var Survey $survey */
        if (!$survey = $this->surveyRepository->find($surveyId)) {
            Error::throwError(Error::S542_SURVEY_NOT_FOUND);
        }

        if ($survey->type !== SurveyType::WEB) {
            Error::throwError(Error::S542_SURVEY_LAUNCH_EXPECTS_WEB);
        }

        $survey->status = SurveyStatus::LAUNCHED;

        $this->surveyRepository->update($survey);

        // add notification here
        if ($survey->pushNotification) {
            $this->sendNotification($surveyId);
        }
    }

    /**
     * For launching SMS/USSD/AUD type surveys
     * @param string $surveyId
     * @param array $data
     */
    public function launchSmsUssdAudSurvey(string $surveyId, array $data): void
    {
        /** @var $survey Survey */
        if (!$survey = $this->surveyRepository->find($surveyId)) {
            Error::throwError(Error::S542_SURVEY_NOT_FOUND);
        }

        switch ($survey->type) {
            /**
             * @noinspection PhpMissingBreakStatementInspection
             * Set audience for AUD case and continue with SMS
             */
            case SurveyType::AUD:
                $search = [
                    'country'    => ['EQUALS', $survey->countries[0]],
                    'ageGroup'   => ['EQUALS', $survey->sample['ageGroup']],
                    'employment' => ['EQUALS', $survey->sample['employment']],
                    'gender'     => ['EQUALS', $survey->sample['gender']],
                ];
                if (in_array(Country::SOUTH_AFRICA, $survey->countries, true)) {
                    $search['race']     = ['EQUALS', $survey->sample['race']];
                    $search['lsmGroup'] = ['EQUALS', $survey->sample['lsmGroup']];
                }

                // TODO: randomise this list
                $survey->audience = $this->respondentRepository->list(0, $survey->expectedCompletes, $search, null, 'mobile');

            // NO BREAK: continue to use SMS case before break

            case SurveyType::SMS:
                $survey->smsCode = $data['smsCode'];
                $this->surveyRepository->update($survey);

                $response = 'Hi, this is a survey on ' . $survey->title . '.\nWe would like your response. Please reply to this SMS to start the survey.\nThanks, Survey54.';
                break;

            case SurveyType::USSD:
                if ($data['ussdCode'] === null) {
                    Error::throwError(Error::S542_USSD_CODE_REQUIRED);
                }
                $survey->smsCode  = $data['smsCode'];
                $survey->ussdCode = $data['ussdCode'];
                $this->surveyRepository->update($survey);

                $response = 'Hi, this is a survey on ' . $survey->title . '.\nWe would like your response. Please enter ' . $survey->ussdCode . ' to start the survey.\nThanks, Survey54.';
                break;
        }

        if ($survey->audience) {
            foreach ($survey->audience as $aud) {
                $this->africasTalkingAdapter->sendSMS($survey->smsCode, $response ?? '', $aud['mobile']);
            }
        }
    }

    /**
     * @param string $userId
     * @return array
     */
    public function getSurveyStats(string $userId): array
    {
        $surveyCompletedSearch = [
            'userId' => ['EQUALS', $userId],
            'status' => ['EQUALS', SurveyStatus::COMPLETED],
        ];

        $totalSurvey = $this->surveyRepository->count($surveyCompletedSearch);
        $allSurvey   = $this->surveyRepository->list(0, 0, ['userId' => ['EQUALS', $userId]], null, '`uuid`, `expectedCompletes`');

        $completeSurveyCount   = 0;
        $totalSurveyRespondent = 0;
        foreach ($allSurvey as $survey) {
            $surveyRespondentSearch = [
                'surveyId' => ['EQUALS', $survey['uuid']],
            ];

            $actualResponseCount   = $this->respondentSurveyRepository->count($surveyRespondentSearch);
            $totalSurveyRespondent += $actualResponseCount;

            if ($actualResponseCount >= (int)$survey['expectedCompletes']) {
                $completeSurveyCount++;
            }
        }

        $surveyLaunchedSearch = [
            'userId' => ['EQUALS', $userId],
            'status' => ['EQUALS', SurveyStatus::LAUNCHED],
        ];

        return [
            'numberOfOpenSurveys'             => $this->surveyRepository->count($surveyLaunchedSearch),
            'averageCompletionRatePercentage' => $totalSurvey ? floor(($completeSurveyCount / $totalSurvey) * 100) : 0,
            'numberOfSurveyRespondents'       => $totalSurveyRespondent,
        ];
    }

    /**
     * @param string $surveyId
     * @return array
     */
    public function getRespondentEmails(string $surveyId): array
    {
        /** @var $survey Survey */
        if (!$survey = $this->surveyRepository->find($surveyId)) {
            Error::throwError(Error::S542_SURVEY_NOT_FOUND);
        }

        $search         = [
            'surveyId' => ['EQUALS', $surveyId],
        ];
        $responsesCount = $this->respondentSurveyRepository->count($search);
        $respondents    = $this->respondentSurveyRepository->list(0, $responsesCount, $search, null, 'respondentId');
        if (!$respondents) {
            return [];
        }
        $respondentIds = array_column($respondents, 'respondentId');

        $search = [
            'uuid' => ['IN', $respondentIds],
        ];
        return $this->respondentRepository->list(0, count($respondentIds), $search, null, 'email');
    }

    /**
     * @param string $surveyId
     * @return array
     */
    public function getStatus(string $surveyId): array
    {
        /** @var $survey Survey */
        if (!$survey = $this->surveyRepository->find($surveyId)) {
            Error::throwError(Error::S542_SURVEY_NOT_FOUND);
        }

        return [
            'expected'   => $survey->expectedCompletes,
            'completed'  => $this->respondentSurveyRepository->countBySurveyStatus($surveyId, RespondentSurveyStatus::COMPLETED),
            'incomplete' => $this->respondentSurveyRepository->countBySurveyStatus($surveyId, RespondentSurveyStatus::STARTED),
        ];
    }

    /**
     * @param string $surveyId
     * @return bool|Survey
     */
    public function find(string $surveyId)
    {
        return $this->surveyRepository->find($surveyId);
    }

    /**
     * @param float $incentive
     * @param string $country
     * @param array $numbers
     * @return array
     */
    public function sendCsvAirtime(float $incentive, string $country, array $numbers): array
    {
        if (!$numbers) {
            Error::throwError(Error::S54_EMPTY_NUMBERS_LIST);
        }

        $succeeded = $failed = 0;

        if (!Country::isValid($country)) {
            Error::throwError(Error::S542_INVALID_COUNTRY);
        }

        foreach ($numbers as $number) {
            $uuid = UUID::generate();
            try {
                $proof = $this->airtimeAdapter->topUp($uuid, $number, $incentive);
                $log   = new AirtimeLogsCsv($uuid, $number, true, $proof, false, null);
                $succeeded++;
            } catch (ExtendedException $e) {
                $error = [
                    'mobile'     => $number,
                    'errorCode'  => $e->getS54ErrorCode(),
                    'devMessage' => $e->getDeveloperMessage(),
                    'incentive'  => $incentive,
                ];
                $log   = new AirtimeLogsCsv($uuid, $number, false, null, true, $error);
                $failed++;
            }
            $this->airtimeCsvRepository->add($log);
        }

        return [
            'succeeded' => $succeeded,
            'failed'    => $failed,
        ];
    }

    /**
     * @param string $surveyId
     * @param int $limit
     * @return array
     */
    public function sendAirtimeForSurvey(string $surveyId, int $limit): array
    {
        /** @var Survey $survey */
        if (!$survey = $this->surveyRepository->find($surveyId)) {
            Error::throwError(Error::S542_SURVEY_NOT_FOUND);
        }

        $search = [
            'surveyId'       => ['EQUALS', $surveyId],
            'status'         => ['EQUALS', RespondentSurveyStatus::COMPLETED],
            'nextQuestionId' => ['EQUALS', count($survey->questions)], // only qualified
            'redeemed'       => ['EQUALS', 0],
            'errored'        => ['EQUALS', 0],
        ];

        if ($this->respondentSurveyRepository->count($search) === 0) {
            $search['errored'] = ['EQUALS', 1]; // previously errored top-ups
        }

        $respondentIds = $this->respondentSurveyRepository->list(0, $limit, $search, ['+createdAt'], '`respondentId`');
        $respondentIds = array_column($respondentIds, 'respondentId');
        $successCount  = $errorCount = 0;

        if ($respondentIds) {
            $search      = [
                'uuid' => ['IN', $respondentIds],
            ];
            $respondents = $this->respondentRepository->list(0, 0, $search, null, '`uuid`, `mobile`, `email`');

            if ($respondents) {
                foreach ($respondents as $respondent) {
                    if (isset($respondent['mobile'])) {
                        $rs = $this->respondentSurveyRepository->findByRespondentSurvey($respondent['uuid'], $surveyId);
                        try {
                            $rs->proof    = $this->airtimeAdapter->topUp($rs->uuid, $respondent['mobile'], $survey->incentive);
                            $rs->redeemed = true;
                            $rs->errored  = false;
                            $rs->error    = null;
                            $successCount++;
                        } catch (ExtendedException $e) {
                            $rs->proof    = null;
                            $rs->redeemed = false;
                            $rs->errored  = true;
                            $rs->error    = [
                                'mobile'     => $respondent['mobile'],
                                'email'      => $respondent['email'],
                                'errorCode'  => $e->getS54ErrorCode(),
                                'devMessage' => $e->getDeveloperMessage(),
                                'incentive'  => $survey->incentive,
                            ];
                            $errorCount++;
                        }
                        $this->respondentSurveyRepository->update($rs);
                    }
                }
            }
        }

        return [
            'successCount' => $successCount,
            'errorCount'   => $errorCount,
        ];
    }

    /**
     * @param int $offset
     * @param int $limit
     * @return array
     */
    public function listAirtimeLogs(int $offset, int $limit): array
    {
        return $this->airtimeCsvRepository->list($offset, $limit);
    }

    /**
     * @param string $surveyId
     * @return array
     */
    public function listAirtimeLogsForSurvey(string $surveyId): array
    {
        /** @var Survey $survey */
        if (!$survey = $this->surveyRepository->find($surveyId)) {
            Error::throwError(Error::S542_SURVEY_NOT_FOUND);
        }

        $search = [
            'surveyId'       => ['EQUALS', $surveyId],
            'status'         => ['EQUALS', RespondentSurveyStatus::COMPLETED],
            'nextQuestionId' => ['EQUALS', count($survey->questions)], // only qualified
        ];
        $rsList = $this->respondentSurveyRepository->list(0, 0, $search, null, 'uuid,respondentId,redeemed,proof,errored,error');

        if (count($rsList) === 0) {
            return [];
        }

        $respondentIds   = array_column($rsList, 'respondentId');
        $search          = [
            'uuid' => ['IN', $respondentIds],
        ];
        $matchingNumbers = $this->respondentRepository->list(0, count($rsList), $search, null, 'uuid,mobile');

        $lookupTable = [];
        foreach ($matchingNumbers as $matchingNumber) {
            $lookupTable[$matchingNumber['uuid']] = $matchingNumber['mobile'];
        }

        $result = [];
        foreach ($rsList as $response) {
            $response['mobile'] = $lookupTable[$response['respondentId'] ?? ''] ?? '';
            $result[]           = [
                'uuid'     => $response['uuid'],
                'mobile'   => $response['mobile'],
                'error'    => $response['error'],
                'errored'  => $response['errored'],
                'redeemed' => $response['redeemed'],
                'proof'    => $response['proof'],
            ];
        }

        return $result;
    }

    /**
     * @param string $respondentId
     * @param bool $history
     * @return array
     */
    public function list(string $respondentId, bool $history): array
    {
        if (!$history) {
            return [
                'Recent Surveys'                       => $this->listDecode($respondentId),
                TagsReserved::BEAUTY_AND_PERSONAL_CARE => $this->listDecode($respondentId, TagsReserved::BEAUTY_AND_PERSONAL_CARE),
                TagsReserved::EDUCATION                => $this->listDecode($respondentId, TagsReserved::EDUCATION),
                TagsReserved::FINANCE                  => $this->listDecode($respondentId, TagsReserved::FINANCE),
                TagsReserved::FOOD_AND_DRINK           => $this->listDecode($respondentId, TagsReserved::FOOD_AND_DRINK),
                TagsReserved::HEALTH                   => $this->listDecode($respondentId, TagsReserved::HEALTH),
                TagsReserved::HOUSING                  => $this->listDecode($respondentId, TagsReserved::HOUSING),
                TagsReserved::RELIGION                 => $this->listDecode($respondentId, TagsReserved::RELIGION),
                TagsReserved::SCIENCE                  => $this->listDecode($respondentId, TagsReserved::SCIENCE),
                TagsReserved::TECHNOLOGY               => $this->listDecode($respondentId, TagsReserved::TECHNOLOGY),
                TagsReserved::TRAVEL                   => $this->listDecode($respondentId, TagsReserved::TRAVEL),
            ];
        }

        // History of surveys COMPLETED by the respondent
        $rsSearch  = [
            'respondentId' => ['EQUALS', $respondentId],
            'status'       => ['EQUALS', RespondentSurveyStatus::COMPLETED],
        ];
        $rsList    = $this->respondentSurveyRepository->list(0, 0, $rsSearch, null, '`surveyId`');
        $rsSurveys = array_column($rsList, 'surveyId');

        if (empty($rsSurveys)) {
            return [];
        }

        $search     = [
            'uuid' => ['IN', $rsSurveys],
        ];
        $select     = '`uuid`, `title`, `description`, `expectedCompletes`, `status`, `tagLabels`, `image`, `incentive`, `incentiveCurrency`';
        $surveyList = $this->surveyRepository->list(0, 0, $search, ['-createdAt'], $select);

        if ($surveyList) {
            foreach ($surveyList as &$item) {
                $item['tagLabels'] = json_decode($item['tagLabels'], true, 512, JSON_THROW_ON_ERROR);
            }
        }
        return $surveyList;
    }

    /**
     * @param string $respondentId
     * @param string|null $term
     * @return array
     */
    public function listDecode(string $respondentId, string $term = null): array
    {
        /*
         * Step 1: Get $surveyIds which is a list of ids of surveys COMPLETED by the respondent
         * Used in $search
         */
        $rsSearch  = [
            'respondentId' => ['EQUALS', $respondentId],
            'status'       => ['EQUALS', RespondentSurveyStatus::COMPLETED],
        ];
        $rsList    = $this->respondentSurveyRepository->list(0, 0, $rsSearch, null, '`surveyId`');
        $surveyIds = array_column($rsList, 'surveyId');

        /*
         * Step 2: Get a $list of LAUNCHED surveys by segmenting on the respondent's fields:
         * country, ageGroup, employment, gender, race
         */
        /** @var Respondent $respondent */
        $respondent = $this->respondentRepository->find($respondentId);
        $country    = $respondent->country;

        $select = '`uuid`, `title`, `description`, `countries`, `expectedCompletes`, `status`, `tagLabels`, `image`, `incentive`, `incentiveCurrency`, `sample`';

        $searchData = [
            'tagLabels' => "$term|",
            'countries' => "$country|",
        ];

        $builder = (new SearchBuilder($searchData))
            ->addTerm('tagLabels', 'JSON', 3)
            ->addTerm('countries', 'JSON', 3);

        $limit = 0;
        if ($term === null) {
            // Five Recent Surveys
            $builder->unset('tagLabels');
            $limit = 5;
        }

        $search           = $builder->getSearch();
        $search['status'] = ['EQUALS', SurveyStatus::LAUNCHED];

        if (count($surveyIds) > 0) {
            $search['uuid'] = ['NOT_IN', $surveyIds]; // Only return launched surveys not yet completed by the user
        }

        $list = $this->surveyRepository->list(0, $limit, $search, ['-createdAt'], $select);

        /*
         * Step 3: Get a list of $nonCompleted surveys, by excluding $surveyIds from $list, and applying segmentation
         * TODO:: This segmentation could have been applied by simple search if demographic sample on survey object is flattened out as opposed to in object
         */
        $nonCompleted = [];
        if ($list) {
            foreach ($list as $item) {
                $item['sample']    = json_decode($item['sample'], true, 512, JSON_THROW_ON_ERROR);
                $item['countries'] = json_decode($item['countries'], true, 512, JSON_THROW_ON_ERROR);

                $result = SegmentationHelper::segmentGetData($respondent, $item); // TODO: change 2nd argument to Survey object after flattening `sample` attribute
                if (!empty($result)) {
                    $nonCompleted[] = $result;
                }
            }
        }
        return $nonCompleted;
    }

    /**
     * @param array $data
     * @return array
     */
    public function listOpen(array $data): array
    {
        // BE: respondent id or demographic info
        $respondentId = $data['respondentId'] ?? null;
        /** @var Respondent $respondent */
        if ($respondentId) {
            if (!$respondent = $this->respondentRepository->find($respondentId)) {
                Error::throwError(Error::S542_RESPONDENT_NOT_FOUND);
            }
            if (!$respondent->demographicCompleted) {
                Error::throwError(Error::S542_DEMOGRAPHIC_INCOMPLETE);
            }
        } else {
            // -- CREATE RESPONDENT USER --
            $respondentId     = $data['uuid'] = UUID::generate();
            $data['ageGroup'] = DobHelper::getAgeGroupFromDOB($data['dateOfBirth']);
            $respondent       = Respondent::build($data);
            if ($this->respondentRepository->findByMobile($respondent->mobile)) {
                Error::throwError(Error::S542_MOBILE_ALREADY_EXIST);
            }
            // Ghost settings
            if ($ghost = $this->ghostRepository->findByMobile($respondent->mobile)) {
                $respondent->ghostMobile = $ghost->ghostMobile;
                $respondent->isGhost     = true;
            }

            // Create respondent
            $this->respondentRepository->add($respondent);
            // Add LSM to respondent
            if (!empty($data['lsmKeys'])) {
                $this->respondentService->addLsm($respondentId, $data['lsmKeys']);
            }
            // Update demographicCompleted
            /** @var Respondent $user */
            $user                       = $this->respondentRepository->find($respondentId);
            $user->demographicCompleted = SegmentationHelper::demographicCompletedCheck($data)['demographicCompleted'];
            $this->respondentRepository->update($user);
        }

        return $this->list($respondentId, false);
    }
}
