<?php

namespace Repository;

use Survey54\Reap\Application\Repository\LogRepository;
use Survey54\Reap\Domain\Log;
use Tests\Unit\AbstractTestCase;

class LogRepositoryTest extends AbstractTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->logRepository = new LogRepository($this->adapter, Log::class);
    }

    public function testAdd(): void
    {
        $this->adapter->method('write')
            ->willReturn(true);

        $actual = $this->logRepository->add($this->log);

        self::assertTrue($actual);
    }

    public function testList(): void
    {
        $logData             = self::$logData;
        $logData['request']  = json_encode($logData['request'], JSON_THROW_ON_ERROR);
        $logData['response'] = json_encode($logData['response'], JSON_THROW_ON_ERROR);

        $this->adapter->method('list')
            ->willReturn([$logData]);

        $actual = $this->logRepository->list(0, 0);

        self::assertEquals([self::$logData], $actual);
    }
}
