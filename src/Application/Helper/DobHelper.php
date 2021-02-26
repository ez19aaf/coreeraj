<?php

namespace Survey54\Reap\Application\Helper;

use Exception;
use Survey54\Library\Domain\Values\AgeGroup;
use Survey54\Library\Utilities\DateTime;
use Survey54\Reap\Framework\Exception\Error;

class DobHelper
{
    /**
     * @param string $dateOfBirth
     * @return string
     */
    public static function getAgeGroupFromDOB(string $dateOfBirth): string
    {
        try {
            $ageGroups           = AgeGroup::toArray();
            $currentDateString   = DateTime::generate('d-m-Y');
            $dateOfBirthReversed = implode("-", array_reverse(explode("-", $dateOfBirth)));
            $currentDateReversed = implode('-', array_reverse(explode('-', $currentDateString)));
            if (strcasecmp($currentDateReversed, $dateOfBirthReversed) <= 0) {
                Error::throwError(Error::S542_INVALID_DATE_OF_BIRTH);
            }
            $currentDate = new \DateTime($currentDateString);
            $birthDate   = new \DateTime((new \DateTime($dateOfBirth))->format('d-m-Y'));
            $age         = $currentDate->diff($birthDate)->y;
            if ($age < 16) {
                Error::throwError(Error::S542_UNDER_AGE);
            }
            foreach ($ageGroups as $ageGroup) {
                $ageGroupCopy = explode('-', $ageGroup);
                if ((count($ageGroupCopy) === 2) && $age >= (int)$ageGroupCopy[0] && $age <= (int)$ageGroupCopy[1]) {
                    return $ageGroup;
                }
            }
        } catch (Exception $e) {
            Error::throwError(Error::S542_INVALID_DATE_OF_BIRTH);
        }
        return AgeGroup::AGE_55_PLUS;
    }
}
