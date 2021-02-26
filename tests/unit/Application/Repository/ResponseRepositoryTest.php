<?php

namespace Tests\Unit\Application\Repository;

use Survey54\Reap\Application\Repository\ResponseRepository;
use Survey54\Reap\Domain\Response;
use Tests\Unit\AbstractTestCase;

class ResponseRepositoryTest extends AbstractTestCase
{
    /** @var array */
    protected static $encoded;

    protected function setUp(): void
    {
        parent::setUp();

        $this->responseRepository = new ResponseRepository($this->adapter, Response::class);

        self::$encoded               = self::$responseData;
        self::$encoded['answerIds']  = json_encode(self::$encoded['answerIds'], JSON_THROW_ON_ERROR);
        self::$encoded['questionId'] = json_encode(self::$encoded['questionId'], JSON_THROW_ON_ERROR);
    }

    public function testAdd_PreWrite(): void
    {
        $this->adapter->method('write')
            ->willReturn(true);

        $actual = $this->responseRepository->add($this->response);

        self::assertTrue($actual);
    }

    public function testFindByRespondentSurveyQuestion(): void
    {
        $this->adapter->method('readBy')
            ->willReturn(self::$encoded);

        $actual = $this->responseRepository->findByRespondentSurveyQuestion('rId', 'sId', 1);

        self::assertEquals(self::$responseData, $actual->toArray());
    }

    public function testFindByRespondentSurvey(): void
    {
        $this->adapter->method('readBy')
            ->willReturn(self::$encoded);

        $actual = $this->responseRepository->findByRespondentSurvey('rId', 'sId');

        self::assertEquals(self::$responseData, $actual->toArray());
    }

    public function testList(): void
    {
        $this->adapter->method('list')
            ->willReturn([self::$encoded]);

        $actual = $this->responseRepository->list(0, 1);

        self::assertEquals([self::$responseData], $actual);
    }

    public function testList_WithSelect(): void
    {
        $this->adapter->method('list')
            ->willReturn([self::$responseData]); // not encoded for select without encoded fields

        $actual = $this->responseRepository->list(0, 1, null, null, 'uuid');

        self::assertEquals([self::$responseData], $actual);
    }
}
