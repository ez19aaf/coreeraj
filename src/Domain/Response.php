<?php

namespace Survey54\Reap\Domain;

use Survey54\Library\Domain\Domain;

class Response extends Domain
{
    public string $respondentId;
    public string $surveyId;
    public int $questionId;
    public ?int $goto = null; // next question
    public ?string $answer = null;
    public ?array $answerIds = null;
    public ?int $answerRank = null;
    public ?int $answerScale = null;
    public ?string $ageGroup = null;
    public ?string $gender = null;
    public ?string $employment = null;
    public ?string $race = null;
    public ?string $lsmGroup = null;
    public ?string $boundTime = null;
    public ?string $boundDate = null;

    /**
     * Response constructor.
     * @param string $uuid
     * @param string $respondentId
     * @param string $surveyId
     * @param int $questionId
     * @param string|null $createdAt
     * @param string|null $updatedAt
     */
    public function __construct(
        string $uuid,
        string $respondentId,
        string $surveyId,
        int $questionId,
        ?string $createdAt = null,
        ?string $updatedAt = null
    ) {
        parent::__construct($uuid, $createdAt, $updatedAt);
        $this->respondentId = $respondentId;
        $this->surveyId     = $surveyId;
        $this->questionId   = $questionId;
    }

    public static function build(array $data, bool $buildByAlias = false): self
    {
        if ($buildByAlias) {
            $data = self::buildByAlias('response_', $data);
        }
        $response              = new self(
            $data['uuid'],
            $data['respondentId'],
            $data['surveyId'],
            $data['questionId'],
            $data['createdAt'] ?? null,
            $data['updatedAt'] ?? null
        );
        $response->goto        = $data['goto'] ?? null;
        $response->answer      = $data['answer'] ?? null;
        $response->answerIds   = $data['answerIds'] ?? null;
        $response->answerRank  = $data['answerRank'] ?? null;
        $response->answerScale = $data['answerScale'] ?? null;
        $response->ageGroup    = $data['ageGroup'] ?? null;
        $response->gender      = $data['gender'] ?? null;
        $response->employment  = $data['employment'] ?? null;
        $response->race        = $data['race'] ?? null;
        $response->lsmGroup    = $data['lsmGroup'] ?? null;
        $response->boundTime   = $data['boundTime'] ?? null;
        $response->boundDate   = $data['boundDate'] ?? null;
        return $response;
    }

    public function jsonSerialize()
    {
        return [
            'uuid'         => $this->uuid,
            'respondentId' => $this->respondentId,
            'surveyId'     => $this->surveyId,
            'questionId'   => $this->questionId,
            'goto'         => $this->goto,
            'answer'       => $this->answer,
            'answerIds'    => $this->answerIds,
            'answerRank'   => $this->answerRank,
            'answerScale'  => $this->answerScale,
            'ageGroup'     => $this->ageGroup,
            'employment'   => $this->employment,
            'gender'       => $this->gender,
            'race'         => $this->race,
            'lsmGroup'     => $this->lsmGroup,
            'boundTime'    => $this->boundTime,
            'boundDate'    => $this->boundDate,
            'createdAt'    => $this->createdAt,
            'updatedAt'    => $this->updatedAt,
        ];
    }
}
