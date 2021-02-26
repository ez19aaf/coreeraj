<?php


namespace Tests\Unit\Application;

use Survey54\Library\Domain\Values\AgeGroup;
use Survey54\Library\Domain\Values\Employment;
use Survey54\Library\Domain\Values\Gender;
use Survey54\Library\Domain\Values\LsmGroup;
use Survey54\Library\Domain\Values\Race;
use Survey54\Library\Utilities\Helper;
use Survey54\Reap\Application\OpenService;
use Tests\Unit\AbstractTestCase;

class OpenServiceTest extends AbstractTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->openService = new OpenService();
    }

    public function testGetEmployments(): void
    {
        $actual = $this->openService->getEmployments();

        self::assertEquals($actual, Employment::toArray());
    }

    public function testGetAgeGroups(): void
    {
        $actual = $this->openService->getAgeGroups();

        self::assertEquals($actual, AgeGroup::toArray());
    }

    public function testGetGenders(): void
    {
        $actual = $this->openService->getGenders();

        self::assertEquals($actual, Gender::toArray());
    }

    public function testGetRaces(): void
    {
        $actual = $this->openService->getRaces();

        self::assertEquals($actual, Race::toArray());
    }

    public function testGetAllLsmRecord(): void
    {
        $actual   = $this->openService->getAllLsmRecord();
        $expected = Helper::decodeJsonFile(LSM_RECORD_JSON);

        self::assertEquals($actual, $expected);
    }

    public function testGetLsmRecord(): void
    {
        $actual   = $this->openService->getLsmRecord();
        $expected = Helper::decodeJsonFile(LSM_RECORD_JSON);

        self::assertEquals($actual, $expected['options']);
    }

    public function testGetLsmGroups(): void
    {
        $actual   = $this->openService->getLsmGroups();
        $expected = LsmGroup::toArray();
        self::assertEquals($expected, $actual);
    }
}
