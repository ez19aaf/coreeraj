<?php

namespace Tests\Unit\Application;

use Exception;
use Survey54\Library\Domain\Values\Employment;
use Survey54\Library\Domain\Values\Gender;
use Survey54\Library\Domain\Values\RespondentSurveyStatus;
use Survey54\Reap\Application\ResponseService;
use Tests\Unit\AbstractTestCase;

class ResponseServiceTest extends AbstractTestCase
{
    private array $data; // $requestData

    protected function setUp(): void
    {
        parent::setUp();

        $this->data             = self::$responseData;
        $this->data['isCint']   = false;
        $this->data['metadata'] = [
            'email'       => null,
            'mobile'      => '+447123456789',
            'ipAddress'   => '192.168.0.1',
            'dateOfBirth' => '20-07-2000',
            'employment'  => Employment::EMPLOYED,
            'gender'      => Gender::FEMALE,
            'lsmKeys'     => ['i1', 'i2', 'i3'],
        ];

        $this->responseService = new ResponseService(
            $this->ghostRepository,
            $this->responseRepository,
            $this->respondentRepository,
            $this->respondentSurveyRepository,
            $this->surveyRepository,
            $this->respondentService,
            $this->textMessageService,
            $this->africasTalkingAdapter,
            $this->ipToCountryAdapter,
            'secret',
        );
    }

    public function testSetup_SurveyCompleted(): void
    {
        $this->expectExceptionCode(400);
        $this->expectException(Exception::class);

        $this->surveyRepository->method('find')
            ->willReturn($this->survey);

        $this->survey->status = 'COMPLETED';
        $this->responseService->setup($this->data);
    }

    public function testSetup_SurveyNotFound(): void
    {
        $this->expectExceptionCode(400);
        $this->expectException(Exception::class);

        $this->surveyRepository->method('find')
            ->willReturn(false);

        $this->responseService->setup($this->data);
    }

    public function testSetup_SurveyCompletedByRespondentID(): void
    {
        $this->expectExceptionCode(400);
        $this->expectException(Exception::class);

        $this->surveyRepository->method('find')
            ->willReturn($this->survey);

        $this->respondentSurveyRepository->method('findByRespondentSurvey')
            ->willReturn($this->respondentSurvey);

        $this->responseService->setup($this->data);
    }

    public function testSetup_RespondentNotFound(): void
    {
        $this->expectExceptionCode(400);
        $this->expectException(Exception::class);

        $this->surveyRepository->method('find')
            ->willReturn($this->survey);

        $this->respondentRepository->method('find')
            ->willReturn(false);

        $this->responseService->setup($this->data);
    }

    public function testSetup_RespondentIDByMobile(): void
    {
        $this->surveyRepository->method('find')
            ->willReturn($this->survey);

        $this->data['respondentId'] = null;

        $this->respondentRepository->method('findByMobile')
            ->willReturn($this->respondent);

        $this->respondentRepository->method('find')
            ->willReturn($this->respondent);

        $actual = $this->responseService->setup($this->data);

        self::assertArrayHasKey('nextQuestion', $actual);
    }

    public function testSetup_RespondentIDIsNull(): void
    {
        $this->surveyRepository->method('find')
            ->willReturn($this->survey);

        $this->data['respondentId']        = null;
        $this->data['metadata']['mobile']  = '1234567890';
        $this->data['metadata']['country'] = 'South Africa';

        $this->respondentRepository->method('find')
            ->willReturn($this->respondent);

        $actual = $this->responseService->setup($this->data);

        self::assertArrayHasKey('nextQuestion', $actual);
    }

    public function testSetup_NonCint_FirstCall(): void
    {
        $this->surveyRepository->method('find')
            ->willReturn($this->survey);

        $this->responseRepository->method('findByRespondentSurvey')
            ->willReturn(false);

        $this->surveyRepository->method('getQuestionFindBySurveyAndQuestion')
            ->willReturn($this->survey->questions[1]);

        $this->respondentRepository->method('find')
            ->willReturn($this->respondent);

        $actual = $this->responseService->setup($this->data);

        self::assertArrayHasKey('nextQuestion', $actual);
    }

    public function testSetup_NonCint_ReturningUser(): void
    {
        $this->surveyRepository->method('find')
            ->willReturn($this->survey);
        $this->surveyRepository->method('getQuestionFindBySurveyAndQuestion')
            ->willReturn($this->survey->questions[1]);

        $this->respondentSurveyRepository->method('findByIpSurvey')
            ->willReturn($this->respondentSurvey);

        $this->responseRepository->method('findByRespondentSurveyQuestion')
            ->willReturn($this->response);
        $this->responseRepository->method('list')
            ->willReturn([self::$responseData]);

        $this->respondentRepository->method('find')
            ->willReturn($this->respondent);

        $actual = $this->responseService->setup($this->data);

        self::assertArrayHasKey('nextQuestion', $actual);
    }

    public function testSetup_Cint_InvalidResponseLink(): void
    {
        $this->expectExceptionCode(400);
        $this->expectException(Exception::class);

        $this->data['isCint'] = true;

        $this->surveyRepository->method('find')
            ->willReturn($this->survey);

        $this->respondentSurveyRepository->method('findByRespondentSurvey')
            ->willReturn(false);

        $this->responseService->setup($this->data);
    }

    public function testSetup_AddRespondentToDB_EmailExist(): void
    {
        $this->expectExceptionCode(400);
        $this->expectException(Exception::class);

        $this->data['isCint'] = true;

        $this->surveyRepository->method('find')
            ->willReturn($this->survey);

        $this->respondentSurveyRepository->method('findByRespondentSurvey')
            ->willReturn(false);

        $this->surveyRepository->method('getQuestionFindBySurveyAndQuestion')
            ->willReturn($this->survey->questions[1]);

        $this->respondentRepository->method('findByEmail')->willReturn(true);

        $this->responseService->setup($this->data);
    }

    public function testSetup_Cint_FirstCall(): void
    {
        $this->data['isCint'] = true;

        $this->surveyRepository->method('find')
            ->willReturn($this->survey);

        $this->respondentRepository->method('find')
            ->willReturn($this->respondent);

        $this->respondentSurveyRepository->method('findByRespondentSurvey')
            ->willReturn(false);

        $this->surveyRepository->method('getQuestionFindBySurveyAndQuestion')
            ->willReturn($this->survey->questions[1]);

        $actual = $this->responseService->setup($this->data);

        self::assertArrayHasKey('nextQuestion', $actual);
    }

    public function testSetup_Cint_addRespondentSurveyRecordOnly(): void
    {
        $this->data['isCint'] = true;

        $this->surveyRepository->method('find')
            ->willReturn($this->survey);

        $this->respondentSurveyRepository->method('findByRespondentSurvey')
            ->willReturn(false);

        $this->respondentRepository->method('find')
            ->willReturn($this->respondent);

        $this->surveyRepository->method('getQuestionFindBySurveyAndQuestion')
            ->willReturn($this->survey->questions[1]);

        $actual = $this->responseService->setup($this->data);

        self::assertArrayHasKey('nextQuestion', $actual);
    }

    public function testSetup_Cint_ReturningUser(): void
    {
        $this->data['isCint'] = true;

        $this->surveyRepository->method('find')
            ->willReturn($this->survey);

        $this->respondentRepository->method('find')
            ->willReturn($this->respondent);

        $this->respondentSurvey->status = RespondentSurveyStatus::STARTED;
        $this->respondentSurveyRepository->method('findByRespondentSurvey')
            ->willReturn($this->respondentSurvey);

        $this->responseRepository->method('list')
            ->willReturn([self::$responseData]);

        $this->surveyRepository->method('getQuestionFindBySurveyAndQuestion')
            ->willReturn($this->survey->questions[1]);

        $actual = $this->responseService->setup($this->data);

        self::assertArrayHasKey('nextQuestion', $actual);
    }

    public function testCreate_LunchedSurveyNotFound(): void
    {
        $this->expectExceptionCode(400);
        $this->expectException(Exception::class);

        $this->surveyRepository->method('find')
            ->willReturn(false);

        $this->responseService->create($this->data);
    }

    public function testCreate_LunchedSurveyCompleted(): void
    {

        $this->expectExceptionCode(400);
        $this->expectException(Exception::class);

        $this->surveyRepository->method('find')
            ->willReturn($this->survey);

        $this->respondentRepository->method('find')
            ->willReturn($this->respondent);

        $this->respondentSurveyRepository->method('findByRespondentSurvey')
            ->willReturn($this->respondentSurvey);

        $this->respondentSurvey->status = RespondentSurveyStatus::COMPLETED;
        $this->responseService->create($this->data);
    }

    public function testCreate_GotoMustBeFuture(): void
    {
        $this->expectExceptionCode(400);
        $this->expectException(Exception::class);

        $this->data['goto']      = 1;
        $this->data['answerIds'] = [1, 2];

        $this->surveyRepository->method('find')
            ->willReturn($this->survey);

        $this->responseRepository->method('findByRespondentSurveyQuestion')
            ->willReturn(false);

        $this->responseService->create($this->data);
    }

    public function testCreate_RequiresAnswerOrAnswerIds(): void
    {
        $this->expectExceptionCode(400);
        $this->expectException(Exception::class);

        $this->data['goto']      = null;
        $this->data['answerIds'] = null;

        $this->surveyRepository->method('find')
            ->willReturn($this->survey);

        $this->responseRepository->method('findByRespondentSurveyQuestion')
            ->willReturn(false);

        $this->responseService->create($this->data);
    }

    public function testCreate_ExistingResponse(): void
    {
        $this->data['goto']      = null;
        $this->data['answerIds'] = [1, 2];

        $this->surveyRepository->method('find')
            ->willReturn($this->survey);
        $this->surveyRepository->method('getQuestionFindBySurveyAndQuestion')
            ->willReturn($this->survey->questions[1]);

        $this->responseRepository->method('findByRespondentSurveyQuestion')
            ->willReturn($this->response);

        $this->respondentRepository->method('find')
            ->willReturn($this->respondent);

        $actual = $this->responseService->create($this->data);

        $expected = [
            'surveyId'       => $this->survey->uuid,
            'nextQuestion'   => $this->survey->questions[1],
            'isEndOfSurvey'  => false,
            'isLastQuestion' => false,
        ];

        self::assertEquals($expected, $actual);
    }

    public function testCreate_QuestionType_SINGLE_CHOICE(): void
    {
        $this->respondentSurvey->status = RespondentSurveyStatus::STARTED;

        $this->surveyRepository->method('find')
            ->willReturn($this->survey);

        $this->respondentRepository->method('find')
            ->willReturn($this->respondent);

        $this->respondentSurveyRepository->method('findByRespondentSurvey')
            ->willReturn($this->respondentSurvey);

        $this->surveyRepository->method('getQuestionFindBySurveyAndQuestion')
            ->willReturn($this->survey->questions[3]);

        $actual   = $this->responseService->create($this->data);
        $expected = [
            'surveyId'       => $this->survey->uuid,
            'nextQuestion'   => $this->survey->questions[3],
            'isEndOfSurvey'  => false,
            'isLastQuestion' => true,
        ];

        self::assertEquals($expected, $actual);
    }

    public function testCreate_QuestionType_SINGLE_CHOICE_withreason(): void
    {
        $this->respondentSurvey->status           = RespondentSurveyStatus::STARTED;
        $this->survey->questions[3]['withReason'] = true;

        $this->surveyRepository->method('find')
            ->willReturn($this->survey);

        $this->respondentRepository->method('find')
            ->willReturn($this->respondent);

        $this->respondentSurveyRepository->method('findByRespondentSurvey')
            ->willReturn($this->respondentSurvey);

        $this->surveyRepository->method('getQuestionFindBySurveyAndQuestion')
            ->willReturn($this->survey->questions[3]);

        $actual   = $this->responseService->create($this->data);
        $expected = [
            'surveyId'       => $this->survey->uuid,
            'nextQuestion'   => $this->survey->questions[3],
            'isEndOfSurvey'  => false,
            'isLastQuestion' => true,
        ];

        self::assertEquals($expected, $actual);
    }

    public function testCreate_QuestionType_MULTIPLE_CHOICE(): void
    {
        $this->respondentSurvey->status = RespondentSurveyStatus::STARTED;

        $this->surveyRepository->method('find')
            ->willReturn($this->survey);

        $this->respondentRepository->method('find')
            ->willReturn($this->respondent);

        $this->respondentSurveyRepository->method('findByRespondentSurvey')
            ->willReturn($this->respondentSurvey);

        $this->surveyRepository->method('getQuestionFindBySurveyAndQuestion')
            ->willReturn($this->survey->questions[0]);

        $actual   = $this->responseService->create($this->data);
        $expected = [
            'surveyId'       => $this->survey->uuid,
            'nextQuestion'   => $this->survey->questions[0],
            'isEndOfSurvey'  => false,
            'isLastQuestion' => false,
        ];

        self::assertEquals($expected, $actual);
    }

    public function testCreate_QuestionType_OPEN_ENDED(): void
    {
        $this->respondentSurvey->status = RespondentSurveyStatus::STARTED;

        $this->data['answerIds'] = null;
        $this->data['answer']    = 'test';

        $this->surveyRepository->method('find')
            ->willReturn($this->survey);

        $this->respondentRepository->method('find')
            ->willReturn($this->respondent);

        $this->respondentSurveyRepository->method('findByRespondentSurvey')
            ->willReturn($this->respondentSurvey);

        $this->surveyRepository->method('getQuestionFindBySurveyAndQuestion')
            ->willReturn($this->survey->questions[1]);

        $actual   = $this->responseService->create($this->data);
        $expected = [
            'surveyId'       => $this->survey->uuid,
            'nextQuestion'   => $this->survey->questions[1],
            'isEndOfSurvey'  => false,
            'isLastQuestion' => false,
        ];

        self::assertEquals($expected, $actual);
    }

    public function testCreate_QuestionType_OPEN_ENDED_noAnswer(): void
    {
        $this->expectExceptionCode(400);
        $this->expectException(Exception::class);

        $this->respondentSurvey->status = RespondentSurveyStatus::STARTED;
        $this->data['answerScale']      = false;

        $this->surveyRepository->method('find')
            ->willReturn($this->survey);

        $this->respondentRepository->method('find')
            ->willReturn($this->respondent);

        $this->respondentSurveyRepository->method('findByRespondentSurvey')
            ->willReturn($this->respondentSurvey);

        $this->surveyRepository->method('getQuestionFindBySurveyAndQuestion')
            ->willReturn($this->survey->questions[1]);

        $this->responseService->create($this->data);
    }

    public function testCreate_QuestionType_SCALE(): void
    {
        $this->respondentSurvey->status = RespondentSurveyStatus::STARTED;

        $this->data['answerIds']   = null;
        $this->data['answerScale'] = 1;

        $this->surveyRepository->method('find')
            ->willReturn($this->survey);

        $this->respondentRepository->method('find')
            ->willReturn($this->respondent);

        $this->respondentSurveyRepository->method('findByRespondentSurvey')
            ->willReturn($this->respondentSurvey);

        $this->surveyRepository->method('getQuestionFindBySurveyAndQuestion')
            ->willReturn($this->survey->questions[2]);

        $actual   = $this->responseService->create($this->data);
        $expected = [
            'surveyId'       => $this->survey->uuid,
            'nextQuestion'   => $this->survey->questions[2],
            'isEndOfSurvey'  => false,
            'isLastQuestion' => false,
        ];

        self::assertEquals($expected, $actual);
    }

    public function testCreate_QuestionType_SCALE_noAnswer(): void
    {
        $this->expectExceptionCode(400);
        $this->expectException(Exception::class);

        $this->respondentSurvey->status = RespondentSurveyStatus::STARTED;

        $this->surveyRepository->method('find')
            ->willReturn($this->survey);

        $this->respondentRepository->method('find')
            ->willReturn($this->respondent);

        $this->respondentSurveyRepository->method('findByRespondentSurvey')
            ->willReturn($this->respondentSurvey);

        $this->surveyRepository->method('getQuestionFindBySurveyAndQuestion')
            ->willReturn($this->survey->questions[2]);

        $this->responseService->create($this->data);
    }

    public function testCreate_QuestionType_default(): void
    {
        $this->expectExceptionCode(400);
        $this->expectException(Exception::class);

        $this->survey->questions[0]['type'] = '';

        $this->respondentSurvey->status = RespondentSurveyStatus::STARTED;

        $this->surveyRepository->method('find')
            ->willReturn($this->survey);

        $this->respondentRepository->method('find')
            ->willReturn($this->respondent);

        $this->respondentSurveyRepository->method('findByRespondentSurvey')
            ->willReturn($this->respondentSurvey);

        $this->surveyRepository->method('getQuestionFindBySurveyAndQuestion')
            ->willReturn($this->survey->questions[0]);

        $this->responseService->create($this->data);
    }

    public function testCreate(): void
    {
        $this->data['goto']      = null;
        $this->data['answerIds'] = [1, 2];

        $this->surveyRepository->method('find')
            ->willReturn($this->survey);
        $this->surveyRepository->method('getQuestionFindBySurveyAndQuestion')
            ->willReturn(self::$surveyData['questions'][0]);

        $this->responseRepository->method('findByRespondentSurveyQuestion')
            ->willReturn(false);

        $this->respondentRepository->method('find')
            ->willReturn($this->respondent);

        $actual = $this->responseService->create($this->data);

        $expected = [
            'surveyId'       => $this->survey->uuid,
            'nextQuestion'   => self::$surveyData['questions'][0],
            'isEndOfSurvey'  => false,
            'isLastQuestion' => false,
        ];

        self::assertEquals($expected, $actual);
    }

    public function testCreate_minusGoto(): void
    {
        $this->respondentSurvey->status = RespondentSurveyStatus::STARTED;

        $this->survey->questions[3]['options'][1]['goto'] = -1;
        $this->data['answerIds']                          = [2];

        $this->surveyRepository->method('find')
            ->willReturn($this->survey);

        $this->respondentRepository->method('find')
            ->willReturn($this->respondent);

        $this->respondentSurveyRepository->method('findByRespondentSurvey')
            ->willReturn($this->respondentSurvey);

        $this->surveyRepository->method('getQuestionFindBySurveyAndQuestion')
            ->willReturn($this->survey->questions[3]);

        $this->respondentSurveyRepository->method('count')
            ->willReturn($this->survey->expectedCompletes);

        $actual   = $this->responseService->create($this->data);
        $expected = [
            'surveyId'       => $this->survey->uuid,
            'nextQuestion'   => null,
            'isEndOfSurvey'  => true,
            'isLastQuestion' => true,
        ];

        self::assertEquals($expected, $actual);
    }

    public function testCreate_GotoNonZero(): void
    {
        $this->respondentSurvey->status = RespondentSurveyStatus::STARTED;

        $this->survey->questions[3]['options'][1]['goto'] = 2;
        $this->data['answerIds']                          = [2];

        $this->surveyRepository->method('find')
            ->willReturn($this->survey);

        $this->respondentRepository->method('find')
            ->willReturn($this->respondent);

        $this->respondentSurveyRepository->method('findByRespondentSurvey')
            ->willReturn($this->respondentSurvey);

        $this->surveyRepository->method('getQuestionFindBySurveyAndQuestion')
            ->willReturn($this->survey->questions[3]);

        $actual   = $this->responseService->create($this->data);
        $expected = [
            'surveyId'       => $this->survey->uuid,
            'nextQuestion'   => $this->survey->questions[3],
            'isEndOfSurvey'  => false,
            'isLastQuestion' => true,
        ];

        self::assertEquals($expected, $actual);
    }

    public function testCreate_respondentUpdate(): void
    {
        $this->respondentSurvey->status = RespondentSurveyStatus::STARTED;

        $this->survey->questions[3]['options'][1]['goto'] = -1;
        $this->data['answerIds']                          = [2];

        $this->surveyRepository->method('find')
            ->willReturn($this->survey);

        $this->respondentRepository->method('find')
            ->willReturn($this->respondent);

        $this->respondentSurveyRepository->method('findByRespondentSurvey')
            ->willReturn($this->respondentSurvey);

        $this->surveyRepository->method('getQuestionFindBySurveyAndQuestion')
            ->willReturn($this->survey->questions[3]);

        $actual   = $this->responseService->create($this->data);
        $expected = [
            'surveyId'       => $this->survey->uuid,
            'nextQuestion'   => null,
            'isEndOfSurvey'  => true,
            'isLastQuestion' => true,
        ];

        self::assertEquals($expected, $actual);
    }

    public function testList(): void
    {
        $this->surveyRepository->method('find')
            ->willReturn($this->survey);

        $this->respondentSurveyRepository->method('list')
            ->willReturn([
                ['respondentId' => 'zz.zzz.zzz', 'restOfData' => 'restOfData'],
                ['respondentId' => 'yy.yyy.yyy', 'restOfData' => 'restOfData'],
            ]);

        $this->responseRepository->method('list')
            ->willReturn([self::$responseData]);

        $search = [
            'offset'   => 0,
            'limit'    => 1,
            'surveyId' => 'xxx.xxx.xxx',
            'orRepeat' => 1,
        ];

        $actual = $this->responseService->list($search);

        self::assertEquals([self::$responseData], $actual);
    }

    public function setUpUssd(): void
    {
        $this->respondentSurvey->status = RespondentSurveyStatus::STARTED;
        $this->surveyRepository->method('findBy')
            ->willReturn($this->survey);
        $this->surveyRepository->method('find')
            ->willReturn($this->survey);
        $this->respondentRepository->method('findByMobile')
            ->willReturn($this->respondent);
        $this->respondentRepository->method('find')
            ->willReturn($this->respondent);
        $this->respondentSurveyRepository->method('findByRespondentSurvey')
            ->willReturn($this->respondentSurvey);
    }

    public function testSetUpByUSSD(): void
    {
        $this->setUpUssd();
        $this->surveyRepository->method('getQuestionFindBySurveyAndQuestion')
            ->willReturn($this->survey->questions[1]);
        $data   = self::$ussdData;
        $actual = $this->responseService->ussdResponse($data);

        $expected = 'CON What is your favourite recipe book?';
        self::assertEquals($expected, $actual);
    }

    public function testSetUpUSSDMultipleChoiceQuestion(): void
    {
        $this->setUpUssd();
        $this->surveyRepository->method('getQuestionFindBySurveyAndQuestion')
            ->willReturn($this->survey->questions[0]);
        $data     = self::$ussdData;
        $actual   = $this->responseService->ussdResponse($data);
        $expected = 'CON What is your favourite food?\n1. Rice\n2. Potato\n';
        self::assertEquals($expected, $actual);
    }

    public function testSetUpUSSDSingleChoiceQuestion(): void
    {
        $this->setUpUssd();
        $this->surveyRepository->method('getQuestionFindBySurveyAndQuestion')
            ->willReturn($this->survey->questions[3]);
        $data = self::$ussdData;

        $actual = $this->responseService->ussdResponse($data);

        $expected = 'CON What is your favourite dessert?\n1. Ice cream\n2. Cake\n';
        self::assertEquals($expected, $actual);
    }

    public function testSetUpUSSDScaleQuestion(): void
    {
        $this->setUpUssd();

        $this->surveyRepository->method('getQuestionFindBySurveyAndQuestion')
            ->willReturn($this->survey->questions[2]);
        $this->responseRepository->method('findByRespondentSurveyQuestion')
            ->willReturn($this->responseScale);

        $data = self::$ussdData;

        $actual   = $this->responseService->ussdResponse($data);
        $expected = 'CON How likely is it that you would recommend us to a friend?';

        self::assertEquals($expected, $actual);
    }

    public function testSetUpUSSDInvalidQuestionType(): void
    {
        $q         = $this->survey->questions[3];
        $q['type'] = 'INVALID';

        $this->setUpUssd();
        $this->surveyRepository->method('getQuestionFindBySurveyAndQuestion')
            ->willReturn($q);

        $data = self::$ussdData;

        $actual   = $this->responseService->ussdResponse($data);
        $expected = 'END Invalid question.';
        self::assertEquals($expected, $actual);
    }

}
