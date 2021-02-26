<?php

namespace Survey54\Reap\Application\Helper;

use DateTime;
use Survey54\Library\Domain\Values\AgeGroup;
use Survey54\Library\Domain\Values\Country;
use Survey54\Library\Domain\Values\Employment;
use Survey54\Library\Domain\Values\Gender;
use Survey54\Library\Domain\Values\LsmGroup;
use Survey54\Library\Domain\Values\Race;
use Survey54\Reap\Domain\Respondent;

class SegmentationHelper
{
    /**
     * For returning surveys to respondents
     * @param Respondent $respondent
     * @param array $item
     * @return array
     */
    public static function segmentGetData(Respondent $respondent, array $item): array
    {
        $ret = [];
        /*
         * Segment by country
         */
        if (in_array($respondent->country, $item['countries'], true)) {
            $sample = $item['sample'];
            /*
             * Segment by ageGroup, employment, gender
             */
            if (isset($sample['ageGroup'], $sample['employment'], $sample['gender']) &&
                in_array($respondent->ageGroup, $sample['ageGroup'], true) &&
                in_array($respondent->employment, $sample['employment'], true) &&
                in_array($respondent->gender, $sample['gender'], true)) {
                /*
                 * Segment by race and lsmGroup (only South Africa respondent and survey)
                 */
                if ($respondent->country === Country::SOUTH_AFRICA && $item['countries'][0] === Country::SOUTH_AFRICA) {
                    if (isset($sample['race'], $sample['lsmGroup']) &&
                        in_array($respondent->race, $sample['race'], true) &&
                        in_array($respondent->lsmGroup, $sample['lsmGroup'], true)) {
                        $item['tagLabels'] = json_decode($item['tagLabels'], true, 512, JSON_THROW_ON_ERROR);
                        unset($item['sample']); // remove sample from payload
                        $ret = $item;
                    }
                } else {
                    $item['tagLabels'] = json_decode($item['tagLabels'], true, 512, JSON_THROW_ON_ERROR);
                    unset($item['sample']); // remove sample from payload
                    $ret = $item;
                }
            }
        }

        return $ret;
    }

    /**
     * For setting demographicCompleted field on Respondent object
     * @param array $data
     * @return array
     */
    public static function demographicCompletedCheck(array $data): array
    {
        $isSA             = $data['country'] === Country::SOUTH_AFRICA;
        $demographicCount = 0;
        $remainingFields  = [];

        if (isset($data['ageGroup']) && in_array($data['ageGroup'], AgeGroup::toArray(), true)) {
            $demographicCount++;
        } else {
            $remainingFields[] = 'ageGroup';
        }
        if (isset($data['dateOfBirth']) && DateTime::createFromFormat('d-m-Y', $data['dateOfBirth']) !== false) {
            $demographicCount++;
        } else {
            $remainingFields[] = 'dateOfBirth';
        }
        if (isset($data['employment']) && in_array($data['employment'], Employment::toArray(), true)) {
            $demographicCount++;
        } else {
            $remainingFields[] = 'employment';
        }
        if (isset($data['gender']) && in_array($data['gender'], Gender::toArray(), true)) {
            $demographicCount++;
        } else {
            $remainingFields[] = 'gender';
        }
        if ($isSA && isset($data['race']) && in_array($data['race'], Race::toArray(), true)) {
            $demographicCount++;
        } else {
            $remainingFields[] = 'race';
        }
        if ($isSA && isset($data['lsmGroup']) && in_array($data['lsmGroup'], LsmGroup::toArray(), true)) {
            $demographicCount++;
        } else {
            $remainingFields[] = 'lsmGroup';
        }

        $data['demographicCompleted'] = false;

        if ($isSA && $demographicCount >= 6) {
            $data['demographicCompleted'] = true;
        } else if (!$isSA && $demographicCount >= 4) {
            $data['demographicCompleted'] = true;
        }

        $data['remainingFields'] = $remainingFields;

        return $data;
    }
}
