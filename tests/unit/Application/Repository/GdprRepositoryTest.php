<?php

namespace Tests\Unit\Application\Repository;

use Survey54\Reap\Application\Repository\GdprRepository;
use Survey54\Reap\Domain\Gdpr;
use Tests\Unit\AbstractTestCase;

class GdprRepositoryTest extends AbstractTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->gdprRepository = new GdprRepository($this->adapter, Gdpr::class);
    }

    public function testFind(): void
    {
        $this->adapter->method('read')
            ->willReturn(self::$gdprData);

        $actual = $this->gdprRepository->find('uuid');

        self::assertEquals(self::$gdprData, $actual->toArray());
    }

    public function testFindByUserId(): void
    {
        $this->adapter->method('readBy')
            ->willReturn(self::$gdprData);

        $actual = $this->gdprRepository->findByUserId('yy.yy.yyy');

        self::assertEquals(self::$gdprData, $actual->toArray());
    }
}
