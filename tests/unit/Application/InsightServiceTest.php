<?php

namespace Tests\Unit\Application;

use Survey54\Reap\Application\InsightService;
use Tests\Unit\AbstractTestCase;

class InsightServiceTest extends AbstractTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->insightService = new InsightService($this->insightRepository);
    }

    public function testCreate(): void
    {
        $data = [
            'surveyId' => 'xxx.xxx.xxx',
            'userId'   => 'yyy.yyy.yyy',
            'summary'  => [
                'This is the first insight',
                'This is the second insight',
                'This is the third insight',
            ],
        ];

        $this->insightService->create($data);

        self::assertTrue(true);
    }

    public function testList(): void
    {
        $this->insightRepository->method('list')
            ->willReturn([self::$insightData]);

        $data['surveyId'] = 'xxx.xxx.xxx';
        $actual           = $this->insightService->list($data);

        self::assertEquals([self::$insightData], $actual);
    }

    public function testCount(): void
    {
        $this->insightRepository->method('count')
            ->willReturn(5);

        $data['surveyId'] = 'xxx.xxx.xxx';
        $actual           = $this->insightService->count($data);

        self::assertEquals(5, $actual);
    }
}