<?php

namespace Tests\Unit\Application\Repository;

use Survey54\Reap\Application\Repository\SurveyRepository;
use Survey54\Reap\Domain\Survey;
use Tests\Unit\AbstractTestCase;

class SurveyRepositoryTest extends AbstractTestCase
{
    /** @var array */
    protected static $encoded;

    protected function setUp(): void
    {
        parent::setUp();

        $this->surveyRepository = new SurveyRepository($this->adapter, Survey::class);

        self::$encoded                     = self::$surveyData;
        self::$encoded['countries']        = json_encode(self::$encoded['countries'], JSON_THROW_ON_ERROR);
        self::$encoded['sample']           = json_encode(self::$encoded['sample'], JSON_THROW_ON_ERROR);
        self::$encoded['questions']        = json_encode(self::$encoded['questions'], JSON_THROW_ON_ERROR);
        self::$encoded['tagIds']           = json_encode(self::$encoded['tagIds'], JSON_THROW_ON_ERROR);
        self::$encoded['tagLabels']        = json_encode(self::$encoded['tagLabels'], JSON_THROW_ON_ERROR);
        self::$encoded['favourite']        = self::$surveyData['favourite'] ? 1 : 0;
        self::$encoded['pushNotification'] = self::$surveyData['pushNotification'] ? 1 : 0;
    }

    public function testFind(): void
    {
        $this->adapter->method('read')
            ->willReturn(self::$encoded);

        $actual = $this->surveyRepository->find('uuid');

        self::assertEquals(self::$surveyData, $actual->toArray());
    }

    public function testAdd(): void
    {
        $this->adapter->method('write')
            ->willReturn(true);

        $actual = $this->surveyRepository->add($this->survey);

        self::assertTrue($actual);
    }

    public function testGetQuestionFindBySurveyAndQuestion(): void
    {
        $question = [
            'id'    => 2,
            'text'  => 'What is your favourite recipe book?',
            'type'  => 'OPEN_ENDED',
            'media' => [
                'type'       => 'NONE',
                'resource'   => null,
                'supplement' => false,
            ],
            'goto'  => 0,
        ];

        $result = [
            [json_encode($question, JSON_THROW_ON_ERROR, 512)],
        ];

        $this->adapter->method('executeQuery')
            ->willReturn($result);

        $actual = $this->surveyRepository->getQuestionFindBySurveyAndQuestion('sId', 1); // 0 indexed

        self::assertEquals($question, $actual);
    }

    public function testCountMultiChoiceOptions(): void
    {
        $this->adapter->method('countRaw')
            ->willReturn(5);

        $actual = $this->surveyRepository->countMultiChoiceOptions('sId', 1); // 0 indexed

        self::assertEquals(5, $actual);
    }
}