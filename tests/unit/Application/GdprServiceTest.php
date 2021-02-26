<?php


namespace Tests\Unit\Application;

use Exception;
use Survey54\Reap\Application\GdprService;
use Tests\Unit\AbstractTestCase;

class GdprServiceTest extends AbstractTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->gdprService = new GdprService(
            $this->gdprRepository,
            $this->respondentRepository,
        );
    }

    public function testSchedulePseudonymisation(): void
    {
        $userId = 'yyy.yyy.yyy';

        $this->gdprService->scheduleDeletion($userId);

        self::assertTrue(true);
    }

    public function testPseudonymiseUser_FOUND(): void
    {
        $userId = 'yyy.yyy.yyy';

        $this->respondentRepository->method('find')
            ->willReturn($this->respondent);

        $this->gdprService->pseudoDeleteUser($userId);

        self::assertTrue(true);
    }

    public function testPseudonymiseUser_NOTFOUND(): void
    {
        $userId = 'yyy.yyy.yyy';

        $this->expectExceptionCode(400);
        $this->expectException(Exception::class);

        $this->respondentRepository->find($userId);

        $this->gdprService->pseudoDeleteUser($userId);
    }
}
