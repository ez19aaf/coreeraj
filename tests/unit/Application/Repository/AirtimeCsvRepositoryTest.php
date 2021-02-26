<?php

namespace Tests\Unit\Application\Repository;

use Survey54\Reap\Application\Repository\AirtimeCsvRepository;
use Survey54\Reap\Domain\AirtimeLogsCsv;
use Tests\Unit\AbstractTestCase;

class AirtimeCsvRepositoryTest extends AbstractTestCase
{
    private static array $encoded;

    protected function setUp(): void
    {
        parent::setUp();

        $this->airtimeCsvRepository = new AirtimeCsvRepository($this->adapter, AirtimeLogsCsv::class);

        self::$encoded             = self::$airtimeLog;
        self::$encoded['error']    = json_encode(self::$encoded['error'], JSON_THROW_ON_ERROR);
        self::$encoded['proof']    = json_encode(self::$encoded['proof'], JSON_THROW_ON_ERROR);
        self::$encoded['errored']  = self::$encoded['errored'] ? 1 : 0;
        self::$encoded['redeemed'] = self::$encoded['redeemed'] ? 1 : 0;
    }

    public function testAdd(): void
    {
        $this->adapter->method('write')
            ->willReturn(true);

        $actual = $this->airtimeCsvRepository->add($this->airtimeLogsCsv);

        self::assertTrue($actual);
    }

    public function testRead(): void
    {
        $this->adapter->method('read')
            ->willReturn(self::$encoded);

        $actual = $this->airtimeCsvRepository->find('uuid');

        self::assertEquals($actual->toArray(), self::$airtimeLog);
    }

    public function testList(): void
    {
        $this->adapter->method('list')
            ->willReturn([self::$encoded]);

        $actual = $this->airtimeCsvRepository->list(0, 1);

        self::assertEquals([self::$airtimeLog], $actual);
    }

    public function testList_WithSelect(): void
    {
        $this->adapter->method('list')
            ->willReturn([self::$airtimeLog]); // not encoded for select without encoded fields

        $actual = $this->airtimeCsvRepository->list(0, 1, null, null, 'uuid');

        self::assertEquals([self::$airtimeLog], $actual);
    }
}
