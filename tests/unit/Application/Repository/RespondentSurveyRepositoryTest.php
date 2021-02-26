<?php

namespace Tests\Unit\Application\Repository;

use Survey54\Reap\Application\Repository\RespondentSurveyRepository;
use Survey54\Reap\Domain\RespondentSurvey;
use Tests\Unit\AbstractTestCase;

class RespondentSurveyRepositoryTest extends AbstractTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->respondentSurveyRepository = new RespondentSurveyRepository($this->adapter, RespondentSurvey::class);
    }

    public function testAdd(): void
    {
        $this->adapter->method('write')
            ->willReturn(true);

        $actual = $this->respondentSurveyRepository->add($this->respondentSurvey);

        self::assertTrue($actual);
    }

    public function testSurveyCompleted(): void
    {
        $this->adapter->method('updateBy')
            ->willReturn(true);

        $actual = $this->respondentSurveyRepository->surveyCompleted('surveyId', 'respondentId');

        self::assertTrue($actual);
    }

    public function testCountBySurveyStatus(): void
    {
        $this->adapter->method('count')
            ->willReturn(1);

        $actual = $this->respondentSurveyRepository->countBySurveyStatus('surveyId', 'status');

        self::assertEquals(1, $actual);
    }

    public function testFindByRespondentSurvey(): void
    {
        $this->adapter->method('readBy')
            ->willReturn(self::$respondentSurveyData);

        $actual = $this->respondentSurveyRepository->findByRespondentSurvey('rId', 'sId');

        self::assertEquals(self::$respondentSurveyData, $actual->toArray());
    }

    public function testFindByIpSurvey(): void
    {
        $this->adapter->method('readBy')
            ->willReturn(self::$respondentSurveyData);

        $actual = $this->respondentSurveyRepository->findByIpSurvey('ip', 'sId');

        self::assertEquals(self::$respondentSurveyData, $actual->toArray());
    }

    public function testGetCompletedSurveysForRespondent(): void
    {
        $this->adapter->method('list')
            ->willReturn([self::$respondentSurveyData]);

        $actual = $this->respondentSurveyRepository->getCompletedSurveysForRespondent('respondentId');

        self::assertEquals([self::$respondentSurveyData], $actual);
    }

    public function testUpdateNextQuestionId(): void
    {
        $identifier = [
            'surveyId'     => $this->survey->uuid,
            'respondentId' => $this->respondent->uuid,
        ];
        $data       = ['nextQuestionId' => 2];
        $this->adapter->method('updateBy')
            ->with($identifier, $data)
            ->willReturn(true);
        $response = $this->respondentSurveyRepository->updateNextQuestionId($this->survey->uuid, $this->respondent->uuid, 2);
        self::assertEquals(true, $response);
    }

    public function testListWithSelect(): void
    {
        $select   = 'nothing,to,be,selected';
        $expected = [$this->respondentSurvey->toArray()];
        $this->adapter->method('list')
            ->with(0, 1)
            ->willReturn($expected);
        $response = $this->respondentSurveyRepository->list(0, 1, null, null, $select);
        self::assertEquals($expected, $response);
    }
}
