<?php

namespace Tests\Unit\Application;

use Exception;
use Survey54\Library\Domain\Values\Country;
use Survey54\Library\Domain\Values\SurveyStatus;
use Survey54\Library\Exception\ExtendedException;
use Survey54\Reap\Application\SurveyService;
use Tests\Unit\AbstractTestCase;

class SurveyServiceTest extends AbstractTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->surveyService = new SurveyService(
            $this->airtimeCsvRepository,
            $this->ghostRepository,
            $this->logRepository,
            $this->respondentRepository,
            $this->respondentSurveyRepository,
            $this->surveyRepository,
            $this->messageService,
            $this->respondentService,
            $this->textMessageService,
            $this->africasTalkingAdapter,
            $this->airtimeAdapter,
        );
    }

    public function testLaunchSurvey(): void
    {
        $survey         = $this->survey;
        $survey->status = SurveyStatus::LAUNCHED;

        $this->surveyRepository->method('find')
            ->willReturn($survey);

        $this->surveyService->launchWebSurvey('surveyId');

        self::assertTrue(true);
    }

    public function testLaunchSurvey_Exception(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionCode(400);

        $this->surveyRepository->method('find')
            ->willReturn(false);

        $this->surveyService->launchWebSurvey('surveyId');
    }

    public function testGetRespondentEmails(): void
    {
        $this->surveyRepository->method('find')
            ->willReturn($this->survey);

        $this->respondentSurveyRepository->method('count')
            ->willReturn(1);

        $this->respondentSurveyRepository->method('list')
            ->willReturn([self::$respondentSurveyData]);

        $this->respondentRepository->method('list')
            ->willReturn([['email' => self::$respondentData['email']]]);

        $actual = $this->surveyService->getRespondentEmails($this->survey->uuid);

        $expected = [
            [
                "email" => $this->respondent->email,
            ],
        ];

        self::assertEquals($expected, $actual);
    }

    public function testGetStatus(): void
    {
        $this->surveyRepository->method('find')
            ->willReturn($this->survey);

        $actual = $this->surveyService->getStatus($this->survey->uuid);

        $expected = [
            'expected'   => 100,
            'completed'  => 0,
            'incomplete' => 0,
        ];

        self::assertEquals($expected, $actual);
    }

    public function testSendAirtimeCsv(): void
    {
        $this->airtimeCsvRepository->method('list')
            ->willReturn([]);

        $this->airtimeCsvRepository->method('list')
            ->willReturn([self::$airtimeLog]);

        $data   = self::$airTimeCsvData;
        $actual = $this->surveyService->sendCsvAirtime($data['incentive'], $data['country'], $data['numbers']);

        $expected = [
            'succeeded' => 2,
            'failed'    => 0,
        ];
        self::assertEquals($expected, $actual);
    }

    public function testGetAirtimeLogs_erroredRespondentSurvey(): void
    {
        $data            = self::$respondentSurveyData;
        $data['errored'] = true;
        $this->surveyRepository->method('find')
            ->willReturn($this->survey);

        $this->respondentSurveyRepository->method('list')
            ->willReturn([$data]);

        $this->respondentRepository->method('list')
            ->willReturn([self::$respondentData]);

        $this->respondentSurveyRepository->method('findByRespondentSurvey')
            ->willReturn($this->respondentSurvey);


        $actual   = $this->surveyService->listAirtimeLogsForSurvey($this->survey->uuid);
        $expected = [[
                         'uuid'     => 'xx.xx.xxx',
                         'mobile'   => '+27100000001',
                         'redeemed' => false,
                         'proof'    => null,
                         'errored'  => true,
                         'error'    => null,
                     ]];

        self::assertEquals($expected, $actual);
    }

    public function testGetAirtimeLogs_redeemedRespondentSurvey(): void
    {
        $this->surveyRepository->method('find')
            ->willReturn($this->survey);

        $this->respondentSurveyRepository->method('list')
            ->willReturn([self::$respondentSurveyRedeemedData]);

        $this->respondentRepository->method('list')
            ->willReturn([self::$respondentData]);

        $this->respondentSurveyRepository->method('findByRespondentSurvey')
            ->willReturn($this->respondentSurveyRedeemed);

        $actual   = $this->surveyService->listAirtimeLogsForSurvey($this->survey->uuid);
        $expected = [[
                         'uuid'     => 'xx.xx.xxx',
                         'mobile'   => '+27100000001',
                         'redeemed' => true,
                         'proof'    => null,
                         'errored'  => false,
                         'error'    => null,
                     ]];
        self::assertEquals($expected, $actual);
    }

    public function testGetAirtimeLogs_surveyNotFound(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionCode(400);
        $this->surveyRepository->method('find')
            ->willReturn(false);

        $this->surveyService->listAirtimeLogsForSurvey($this->survey->uuid);
    }

    public function testGetAirtimeLogs_noRespondentIds(): void
    {
        $this->surveyRepository->method('find')
            ->willReturn($this->survey);

        $this->respondentSurveyRepository->method('list')
            ->willReturn([]);

        $actual = $this->surveyService->listAirtimeLogsForSurvey($this->survey->uuid);

        $expected = [];
        self::assertEquals($expected, $actual);
    }

    public function testSendAirtime(): void
    {
        $this->surveyRepository->method('find')
            ->willReturn($this->survey);

        $this->respondentSurveyRepository->method('list')
            ->willReturn([$this->respondentSurvey]);

        $this->respondentRepository->method('list')
            ->willReturn([self::$respondentData]);

        $this->respondentSurveyRepository->method('findByRespondentSurvey')
            ->willReturn($this->respondentSurvey);

        $actual = $this->surveyService->sendAirtimeForSurvey($this->survey->uuid, 0);

        $expected = [
            'successCount' => 1,
            'errorCount'   => 0,
        ];

        self::assertEquals($expected, $actual);
    }

    public function testSendAirtime_topUpException(): void
    {
        $this->surveyRepository->method('find')
            ->willReturn($this->survey);

        $this->respondentSurveyRepository->method('list')
            ->willReturn([$this->respondentSurvey]);

        $this->respondentRepository->method('list')
            ->willReturn([self::$respondentData]);

        $this->respondentSurveyRepository->method('findByRespondentSurvey')
            ->willReturn($this->respondentSurvey);

        $this->airtimeAdapter->method('topUp')
            ->will(self::throwException(new ExtendedException));

        $actual = $this->surveyService->sendAirtimeForSurvey($this->survey->uuid, 0);

        $expected = [
            'successCount' => 0,
            'errorCount'   => 1,
        ];

        self::assertEquals($expected, $actual);
    }

    public function testSendAirtime_respondentsLoopBreak(): void
    {
        $this->surveyRepository->method('find')
            ->willReturn($this->survey);

        $this->respondentSurveyRepository->method('list')
            ->willReturn([$this->respondentSurvey]);

        $this->respondentRepository->method('list')
            ->willReturn([self::$respondentData]);

        $this->respondentSurveyRepository->method('findByRespondentSurvey')
            ->willReturn($this->respondentSurvey);

        $actual = $this->surveyService->sendAirtimeForSurvey($this->survey->uuid, 0);

        $expected = [
            'successCount' => 1,
            'errorCount'   => 0,
        ];

        self::assertEquals($expected, $actual);
    }

    public function testSendAirtime_noRespondentIds(): void
    {
        $this->surveyRepository->method('find')
            ->willReturn($this->survey);

        $actual = $this->surveyService->sendAirtimeForSurvey($this->survey->uuid, 0);

        $expected = [
            'successCount' => 0,
            'errorCount'   => 0,
        ];

        self::assertEquals($expected, $actual);
    }

    public function testSendAirtime_surveyNotFound(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionCode(400);
        $this->surveyRepository->method('find')
            ->willReturn(false);

        $this->surveyService->sendAirtimeForSurvey($this->survey->uuid, 0);
    }

    public function testList(): void
    {
        $surveyData              = self::$surveyData;
        $surveyData['tagLabels'] = json_encode($surveyData['tagLabels']);
        $surveyData['sample']    = json_encode($surveyData['sample']);
        $surveyData['countries'] = json_encode($surveyData['countries']);
        $this->respondentRepository->method('find')
            ->willReturn($this->respondent);

        $this->surveyRepository->method('list')
            ->willReturn([$surveyData]);

        $actual = $this->surveyService->list($this->survey->countries[0], false);

        $surveyData = self::$surveyData;
        unset($surveyData['sample']);

        $expected = [
            'Recent Surveys'         => [$surveyData],
            'Beauty & Personal Care' => [$surveyData],
            'Education'              => [$surveyData],
            'Finance'                => [$surveyData],
            'Food & Drink'           => [$surveyData],
            'Health'                 => [$surveyData],
            'Housing'                => [$surveyData],
            'Religion'               => [$surveyData],
            'Science'                => [$surveyData],
            'Technology'             => [$surveyData],
            'Travel'                 => [$surveyData],
        ];

        self::assertEquals($expected, $actual);
    }

    public function testList_country_NIGERIA(): void
    {
        $surveyData              = self::$surveyData;
        $surveyData['tagLabels'] = json_encode($surveyData['tagLabels']);
        $surveyData['sample']    = json_encode($surveyData['sample']);
        $surveyData['countries'] = json_encode($surveyData['countries']);

        $respondent          = $this->respondent;
        $respondent->country = Country::NIGERIA;

        $this->respondentRepository->method('find')
            ->willReturn($respondent);

        $this->surveyRepository->method('list')
            ->willReturn([$surveyData]);

        $actual = $this->surveyService->list($this->survey->countries[0], false);

        $surveyData = self::$surveyData;
        unset($surveyData['sample']);

        $expected = [
            'Recent Surveys'         => [],
            'Beauty & Personal Care' => [],
            'Education'              => [],
            'Finance'                => [],
            'Food & Drink'           => [],
            'Health'                 => [],
            'Housing'                => [],
            'Religion'               => [],
            'Science'                => [],
            'Technology'             => [],
            'Travel'                 => [],
        ];

        self::assertEquals($expected, $actual);
    }

    public function testList_history_true(): void
    {
        $surveyData              = self::$surveyData;
        $surveyData['tagLabels'] = json_encode($surveyData['tagLabels']);

        $this->respondentSurveyRepository->method('list')
            ->willReturn([$this->respondentSurvey->toArray()]);

        $this->surveyRepository->method('list')
            ->willReturn([$surveyData]);

        $actual = $this->surveyService->list($this->survey->countries[0], true);

        $expected = [$this->survey->toArray()];

        self::assertEquals($expected, $actual);
    }

    public function testList_history_true_empty(): void
    {
        $surveyData              = self::$surveyData;
        $surveyData['tagLabels'] = json_encode($surveyData['tagLabels']);

        $this->respondentSurveyRepository->method('list')
            ->willReturn([]);

        $actual = $this->surveyService->list($this->survey->countries[0], true);

        $expected = [];

        self::assertEquals($expected, $actual);
    }

    public function testFind(): void
    {
        $this->surveyRepository->method('find')
            ->willReturn((array)$this->survey);

        $actual = $this->surveyService->find($this->survey->uuid);

        self::assertEquals((array)$this->survey, $actual);
    }
}
