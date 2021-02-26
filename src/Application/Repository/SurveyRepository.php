<?php

namespace Survey54\Reap\Application\Repository;

use Survey54\Library\Repository\Repository;

class SurveyRepository extends Repository
{
    protected function postRead(array &$data): void
    {
        $this->decode($data['audience']);
        $this->decode($data['countries']);
        $this->decode($data['sample']);
        $this->decode($data['questions']);
        $this->decode($data['tagIds']);
        $this->decode($data['tagLabels']);
        $this->decode($data['recurrence']);
        $data['favourite']        = (int)$data['favourite'] === 1;
        $data['pushNotification'] = (int)$data['pushNotification'] === 1;
    }

    protected function preWrite(array &$data): void
    {
        $this->encode($data['audience']);
        $this->encode($data['countries']);
        $this->encode($data['sample']);
        $this->encode($data['questions']);
        $this->encode($data['tagIds']);
        $this->encode($data['tagLabels']);
        $this->encode($data['recurrence']);
        $data['favourite']        = $data['favourite'] ? 1 : 0;
        $data['pushNotification'] = $data['pushNotification'] ? 1 : 0;
    }

    /**
     * Used for goto
     * @param string $surveyId
     * @param int $questionId
     * @return array|null
     */
    public function getQuestionFindBySurveyAndQuestion(string $surveyId, int $questionId): ?array
    {
        $query = "SELECT questions->>'$[$questionId]' FROM `survey` WHERE `uuid` = '$surveyId'"; // $questionId is 0 indexed

        $result = $this->adapter->executeQuery($query);
        $result = reset($result[0]);
        $result = json_decode($result ?? json_encode(null, JSON_THROW_ON_ERROR), true, 512, JSON_THROW_ON_ERROR);

        return $result ?? null;
    }

    /**
     * Requires questions in survey to always be in ascending order for $index to work
     * @param string $surveyId
     * @param int $questionId
     * @return int
     */
    public function countMultiChoiceOptions(string $surveyId, int $questionId): int
    {
        $index = $questionId - 1;
        $query = "SELECT JSON_LENGTH(questions->>'$[$index].options[*]') FROM `survey` " .
            "WHERE `uuid` = '$surveyId' AND questions->>'$[$index].type' = 'MULTIPLE_CHOICE'";
        return $this->adapter->countRaw($query);
    }
}
