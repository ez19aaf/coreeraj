<?php

namespace Survey54\Reap\Application\Repository;

use Survey54\Library\Domain\Values\IntegrationType;
use Survey54\Library\Domain\Values\RespondentSurveyStatus;
use Survey54\Library\Repository\Repository;
use Survey54\Reap\Domain\RespondentSurvey;

class RespondentSurveyRepository extends Repository
{
    protected function postRead(array &$data): void
    {
        $data['redeemed'] = (int)$data['redeemed'] === 1;
        $data['errored']  = (int)$data['errored'] === 1;
        $this->decode($data['error']);
        $this->decode($data['proof']);
        $this->decode($data['gotoMap']);
    }

    protected function preWrite(array &$data): void
    {
        $data['redeemed'] = $data['redeemed'] ? 1 : 0;
        $data['errored']  = $data['errored'] ? 1 : 0;
        $this->encode($data['error']);
        $this->encode($data['proof']);
        $this->encode($data['gotoMap']);
    }

    /**
     * @param string $surveyId
     * @param string $respondentId
     * @return bool
     */
    public function surveyCompleted(string $surveyId, string $respondentId): bool
    {
        $identifier = [
            'surveyId'     => $surveyId,
            'respondentId' => $respondentId,
        ];
        $data       = [
            'status' => RespondentSurveyStatus::COMPLETED,
        ];
        return $this->updateBy($identifier, $data);
    }

    /**
     * @param string $surveyId
     * @param string $respondentId
     * @param int $questionId
     * @return bool
     */
    public function updateNextQuestionId(string $surveyId, string $respondentId, int $questionId): bool
    {
        $identifier = [
            'surveyId'     => $surveyId,
            'respondentId' => $respondentId,
        ];
        $data       = [
            'nextQuestionId' => $questionId,
        ];
        return $this->adapter->updateBy($identifier, $data);
    }

    /**
     * @param string $surveyId
     * @param string $status
     * @return int
     */
    public function countBySurveyStatus(string $surveyId, string $status): int
    {
        $search = [
            'surveyId' => ['EQUALS', $surveyId],
            'status'   => ['EQUALS', $status],
        ];
        return $this->count($search);
    }

    /**
     * @param string $respondentId
     * @param string $surveyId
     * @return bool|RespondentSurvey
     */
    public function findByRespondentSurvey(string $respondentId, string $surveyId)
    {
        $search = [
            'respondentId' => ['EQUALS', $respondentId],
            'surveyId'     => ['EQUALS', $surveyId],
        ];
        return $this->findBy($search);
    }

    /**
     * @param string $ipAddress
     * @param string $surveyId
     * @return bool|RespondentSurvey
     */
    public function findByIpSurvey(string $ipAddress, string $surveyId)
    {
        $search = [
            'ipAddress' => ['EQUALS', $ipAddress],
            'surveyId'  => ['EQUALS', $surveyId],
        ];
        return $this->findBy($search);
    }

    /**
     * @param array $surveyIds
     * @return mixed
     */
    public function findBySurveyIds(array $surveyIds)
    {
        $search['surveyId'] = ['IN', $surveyIds];
        return $this->list(0, 5, $search);
    }

    /**
     * @param string $respondentId
     * @return mixed
     */
    public function findByCINTRespondentId(string $respondentId)
    {
        $search = [
            'respondentId'    => ['EQUALS', $respondentId],
            'integrationType' => ['EQUALS', IntegrationType::CINT],
        ];
        return $this->list(0, 5, $search, ['-createdAt']);
    }


    /**
     * @param string $respondentId
     * @return array
     */
    public function getCompletedSurveysForRespondent(string $respondentId): array
    {
        $search = [
            'respondentId' => ['EQUALS', $respondentId],
            'status'       => ['EQUALS', RespondentSurveyStatus::COMPLETED],
        ];
        return $this->list(0, 0, $search);
    }

    /**
     * @param int $offset
     * @param int $limit
     * @param array|null $search
     * @param array|null $sort
     * @param string|null $select
     * @return array
     */
    public function list(int $offset, int $limit, ?array $search = null, ?array $sort = null, ?string $select = null): array
    {
        $rs = parent::list($offset, $limit, $search, $sort, $select);

        if ($select !== null) {
            $arr = explode(',', $select);
            if (!(in_array('redeemed', $arr, true) && in_array('errored', $arr, true))) {
                return $rs;
            }
        }

        if ($rs) {
            foreach ($rs as &$data) {
                $this->postRead($data);
            }
        }
        return $rs;
    }
}
