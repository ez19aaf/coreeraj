<?php

namespace Survey54\Reap\Application;

use Survey54\Library\Domain\Values\AgeGroup;
use Survey54\Library\Domain\Values\Employment;
use Survey54\Library\Domain\Values\Gender;
use Survey54\Library\Domain\Values\LsmGroup;
use Survey54\Library\Domain\Values\Race;
use Survey54\Library\Utilities\Helper;

class OpenService
{
    /**
     * @return array
     */
    public function getAgeGroups(): array
    {
        return AgeGroup::toArray();
    }

    /**
     * @return array
     */
    public function getEmployments(): array
    {
        return Employment::toArray();
    }

    /**
     * @return array
     */
    public function getGenders(): array
    {
        return Gender::toArray();
    }

    /**
     * @return array
     */
    public function getLsmGroups(): array
    {
        return LsmGroup::toArray();
    }

    /**
     * @return array
     */
    public function getRaces(): array
    {
        return Race::toArray();
    }

    /**
     * @return array
     */
    public function getAllLsmRecord(): array
    {
        return Helper::decodeJsonFile(__DIR__ . '/assets/json/lsm-record.json');
    }

    /**
     * @return array
     */
    public function getLsmRecord(): array
    {
        $lsmRecord = Helper::decodeJsonFile(__DIR__ . '/assets/json/lsm-record.json');
        return $lsmRecord['options'];
    }
}
