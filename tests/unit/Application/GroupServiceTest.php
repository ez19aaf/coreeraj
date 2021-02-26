<?php
namespace Tests\Unit\Application;
Use Exception;
use Survey54\Library\Domain\Values\LsmGroup;
use Survey54\Library\Domain\Values\Race;
use Survey54\Reap\Application\GroupService;
use Tests\Unit\AbstractTestCase;

class GroupServiceTest extends AbstractTestCase
{
    private array $data;
    private array $searchGroupData;

    protected function setUp(): void
    {
        parent::setUp();
        $this->data             = self::$groupData;
        $this->searchGroupData  = self::$groupSearchData;
        $this->groupService = new GroupService(
            $this->groupRepository,
            $this->respondentRepository
        );
    }

    public function testCreateError(): void
    {
        $this->expectExceptionCode(400);
        $this->expectException(Exception::class);
        $this->respondentRepository->method('list')
            ->willReturn(array());
        $this->groupService->create($this->searchGroupData);
        self::assertTrue(True);
    }

    public function testLsmIsEmpty():void
    {
        $data = $this->searchGroupData;
        $data['race'] = [Race::ASIAN, Race::BLACK, Race::COLOURED];

        $this->respondentRepository->method('list')
            ->willReturn([self::$respondentData['mobile']]);

        $this->groupRepository->method('add')
            ->willReturn(false);

        $actual = $this->groupService->create($data);

        $expected = [ "group"=> false ];

        self::assertEquals($expected, $actual);
    }

    public function testRaceIsEmpty():void
    {
        $data = $this->searchGroupData;
        $data['lsmGroup'] = [LsmGroup::LSM_1_4, LsmGroup::LSM_9_10, LsmGroup::LSM_5_6, LsmGroup::LSM_7_8];

        $this->respondentRepository->method('list')
            ->willReturn([self::$respondentData['mobile']]);

        $this->groupRepository->method('add')
            ->willReturn(true);

        $actual = $this->groupService->create($data);

        $expected = [ "group"=> true ];

        self::assertEquals($expected, $actual);
    }

    public function testLsmAndRaceIsEmpty():void
    {
        $data = $this->searchGroupData;

        $this->respondentRepository->method('list')
            ->willReturn([self::$respondentData['mobile']]);

        $this->groupRepository->method('add')
            ->willReturn(true);

        $actual = $this->groupService->create($data);

        $expected = [ "group"=> true ];

        self::assertEquals($expected, $actual);
    }

    public function testLsmAndRace():void
    {
        $data = $this->searchGroupData;
        $data['race'] = [Race::ASIAN, Race::BLACK, Race::COLOURED];
        $data['lsmGroup'] = [LsmGroup::LSM_1_4, LsmGroup::LSM_9_10, LsmGroup::LSM_5_6, LsmGroup::LSM_7_8];

        $this->respondentRepository->method('list')
            ->willReturn([self::$respondentData['mobile']]);

        $this->groupRepository->method('add')
            ->willReturn(true);

        $actual = $this->groupService->create($data);

        $expected = [ "group"=> true ];

        self::assertEquals($expected, $actual);
    }

    public function testUpdateGroup_Error(): void
    {
        $this->expectExceptionCode(404);
        $this->expectException(Exception::class);
        $this->groupRepository->find(self::$groupSearchData['uuid']);

        $this->groupService->update(self::$groupSearchData);
        self::assertTrue(True);
    }

    public function testUpdateGroup(): void
    {
        $request = $this->searchGroupData;

        $request['uuid'] = $this->data['uuid'];
        $this->groupRepository->method('find')
            ->willReturn($this->data);

        $this->respondentRepository->method('list')
            ->willReturn([self::$respondentData['mobile']]);

        $request['audience'] = [self::$respondentData['mobile']];

        $this->groupRepository->method('update')
            ->willReturn(true);

        $actual = $this->groupService->update($request);
        $expected = [ 'group' => true ];
        self::assertEquals($expected, $actual );
    }

    public function testDelete(): void
    {
        $data = [
            'uuid' => [
                $this->group->uuid
            ]
        ];
        $this->groupRepository->method('find')
            ->willReturn($this->group);

        $this->groupService->delete($data);

        self::assertTrue(true);
    }

    public function testDeleteGroupNotFound(): void
    {
        $this->expectExceptionCode(404);
        $this->expectException(Exception::class);

        $this->groupRepository->method('find')
            ->willReturn(null);

        $this->groupService->delete([$this->group->uuid]);

        self::assertTrue(true);
    }
}
