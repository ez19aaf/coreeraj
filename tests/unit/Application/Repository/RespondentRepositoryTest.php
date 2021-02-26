<?php

namespace Tests\Unit\Application\Repository;

use Survey54\Reap\Application\Repository\RespondentRepository;
use Survey54\Reap\Domain\Respondent;
use Tests\Unit\AbstractTestCase;

class RespondentRepositoryTest extends AbstractTestCase
{
    /** @var array */
    protected static $encoded;

    protected function setUp(): void
    {
        parent::setUp();

        $this->respondentRepository = new RespondentRepository($this->adapter, Respondent::class);

        self::$encoded                         = self::$respondentData;
        self::$encoded['demographicCompleted'] = self::$encoded['demographicCompleted'] ? 1 : 0;
    }

    public function testAdd_PreWrite(): void
    {
        $this->adapter->method('write')
            ->willReturn(true);

        $actual = $this->respondentRepository->add($this->respondent);

        self::assertTrue($actual);
    }

    public function testFind_PostRead(): void
    {
        $this->adapter->method('read')
            ->willReturn(self::$encoded);

        $actual = $this->respondentRepository->find('uuid');

        self::assertEquals(self::$respondentData, $actual->toArray());
    }

    public function testFindByEmail(): void
    {
        $this->adapter->method('readBy')
            ->willReturn(self::$encoded);

        $actual = $this->respondentRepository->findByEmail('email');

        self::assertEquals(self::$respondentData, $actual->toArray());
    }

    public function testFindByMobile(): void
    {
        $this->adapter->method('readBy')
            ->willReturn(self::$encoded);

        $actual = $this->respondentRepository->findByMobile('mobile');

        self::assertEquals(self::$respondentData, $actual->toArray());
    }

    public function testWillList(): void
    {
        $list = [$this->respondent->toArray()];
        $this->adapter->method('list')
            ->willReturn($list);

        $actual = $this->respondentRepository->list(0, 1);
        self::assertEquals($list, $actual);
    }

    public function testWillListWithSelect(): void
    {
        $select = 'nothing,to,be,selected';
        $list   = [$this->respondent->toArray()];
        $this->adapter->method('list')
            ->willReturn($list);

        $actual = $this->respondentRepository->list(0, 1, null, null, $select);
        self::assertEquals($list, $actual);
    }

    public function testListSearchNotNull(): void
    {
        $list = [$this->respondent->toArray()];
        $this->adapter->method('list')
            ->willReturn($list);

        $search['uuid'] = ['IN', ['uuid1', 'uuid2']];

        $actual = $this->respondentRepository->list(0, 1, $search);
        self::assertEquals($list, $actual);
    }

    public function testCount(): void
    {
        $this->adapter->method('count')
            ->willReturn(2);

        $actual = $this->respondentRepository->count();
        self::assertEquals(2, $actual);
    }

    public function testCountSearchNotNull(): void
    {
        $this->adapter->method('count')
            ->willReturn(2);

        $search['uuid'] = ['IN', ['uuid1', 'uuid2']];

        $actual = $this->respondentRepository->count($search);
        self::assertEquals(2, $actual);
    }
}
