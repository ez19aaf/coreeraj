<?php

namespace Tests\Unit\Application\Repository;

use Survey54\Reap\Application\Repository\InsightRepository;
use Survey54\Reap\Domain\Insight;
use Tests\Unit\AbstractTestCase;

class InsightRepositoryTest extends AbstractTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->insightRepository = new InsightRepository($this->adapter, Insight::class);
    }

    public function testFind(): void
    {
        $this->adapter->method('read')
            ->willReturn(self::$insightData);

        $actual = $this->insightRepository->find('uuid');

        self::assertEquals(self::$insightData, $actual->toArray());
    }
}