<?php

namespace Survey54\Reap\Application\Repository;

use Survey54\Library\Repository\Repository;

class ResponseRepository extends Repository
{
    protected function postRead(array &$data): void
    {
        $this->decode($data['answerIds']);
        $data['questionId'] = (int)$data['questionId'];
    }

    protected function preWrite(array &$data): void
    {
        $this->encode($data['answerIds']);
    }

    /**
     * @param string $groupBy
     * @param array $search
     * @return array
     */
    public function listGroupBy(string $groupBy, array $search): array
    {
        return $this->adapter->listGroupBy($groupBy, $search);
    }

    /**
     * @param string $respondentId
     * @param string $surveyId
     * @param int $questionId
     * @return mixed
     */
    public function findByRespondentSurveyQuestion(string $respondentId, string $surveyId, int $questionId)
    {
        $search = [
            'respondentId' => ['EQUALS', $respondentId],
            'surveyId'     => ['EQUALS', $surveyId],
            'questionId'   => ['EQUALS', $questionId],
        ];
        return $this->findBy($search);
    }

    /**
     * @param string $respondentId
     * @param string $surveyId
     * @return mixed
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
     * @param int $offset
     * @param int $limit
     * @param array|null $search
     * @param array|null $sort
     * @param string|null $select
     * @return array
     */
    public function list(int $offset, int $limit, ?array $search = null, ?array $sort = null, ?string $select = null): array
    {
        $responses = parent::list($offset, $limit, $search, $sort, $select);

        if ($select !== null) {
            $arr = explode(',', $select);
            if (!(in_array('answerIds', $arr, true) &&
                in_array('questionId', $arr, true)
            )) {
                return $responses;
            }
        }

        if ($responses) {
            foreach ($responses as &$data) {
                $this->postRead($data);
            }
        }
        return $responses;
    }
}
