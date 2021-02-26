<?php

namespace Survey54\Reap\Application\Repository;

use Survey54\Library\Domain\Values\UserStatus;
use Survey54\Library\Repository\Repository;
use Survey54\Reap\Domain\Respondent;

class RespondentRepository extends Repository
{
    protected function postRead(array &$data): void
    {
        $this->decode($data['profileImage']);
        $this->decode($data['lsm']);
        $data['demographicCompleted']    = (bool)$data['demographicCompleted'];
        $data['convertedFromOpenSurvey'] = (bool)($data['convertedFromOpenSurvey'] ?? 0);
        $data['markedForDeletion']       = (bool)($data['markedForDeletion'] ?? 0);
        $data['isSample']                = (bool)($data['isSample'] ?? 0);
        $data['isGhost']                 = (bool)($data['isGhost'] ?? 0);
        $data['promptReview']            = (bool)($data['promptReview'] ?? 0);
    }

    protected function preWrite(array &$data): void
    {
        $this->encode($data['profileImage']);
        $this->encode($data['lsm']);
        $data['demographicCompleted']    = $data['demographicCompleted'] ? 1 : 0;
        $data['convertedFromOpenSurvey'] = $data['convertedFromOpenSurvey'] ?? false ? 1 : 0;
        $data['markedForDeletion']       = $data['markedForDeletion'] ?? false ? 1 : 0;
        $data['isSample']                = $data['isSample'] ?? false ? 1 : 0;
        $data['isGhost']                 = $data['isGhost'] ?? false ? 1 : 0;
        $data['promptReview']            = $data['promptReview'] ?? false ? 1 : 0;
    }

    /**
     * @param string $email
     * @return bool|Respondent
     */
    public function findByEmail(string $email)
    {
        $search['email'] = ['EQUALS', $email];
        return $this->findBy($search);
    }

    /**
     * @param string $mobile
     * @return bool|Respondent
     */
    public function findByMobile(string $mobile)
    {
        $search['mobile'] = ['EQUALS', $mobile];
        return $this->findBy($search);
    }

    public function count(?array $search = null): int
    {
        $actual = [
            'isSample'          => ['EQUALS', 0],
            'markedForDeletion' => ['EQUALS', 0],
            'userStatus'        => ['NOT_EQUALS', UserStatus::DELETED],
        ];
        if ($search === null) {
            $search = $actual;
        } else {
            $search = array_merge($actual, $search);
        }

        return parent::count($search);
    }

    public function list(int $offset, int $limit, ?array $search = null, ?array $sort = null, ?string $select = null): array
    {
        $actual = [
            'isSample'          => ['EQUALS', 0],
            'markedForDeletion' => ['EQUALS', 0],
            'userStatus'        => ['NOT_EQUALS', UserStatus::DELETED],
        ];
        if ($search === null) {
            $search = $actual;
        } else {
            $search = array_merge($actual, $search);
        }

        $respondents = parent::list($offset, $limit, $search, $sort, $select);

        if ($select !== null) {
            $arr = explode(',', $select);
            if (!(in_array('profileImage', $arr, true) &&
                in_array('lsm', $arr, true) &&
                in_array('isCint', $arr, true) &&
                in_array('demographicCompleted', $arr, true) &&
                in_array('convertedFromOpenSurvey', $arr, true)
            )) {
                return $respondents;
            }
        }

        if ($respondents) {
            foreach ($respondents as &$data) {
                $this->postRead($data);
            }
        }
        return $respondents;
    }
}
