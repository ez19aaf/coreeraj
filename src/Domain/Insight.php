<?php

namespace Survey54\Reap\Domain;

use Survey54\Library\Domain\Domain;

class Insight extends Domain
{
    public string $userId;
    public string $surveyId;
    public string $summary;

    /**
     * Insight constructor.
     * @param string $uuid
     * @param string $userId
     * @param string $surveyId
     * @param string $summary
     * @param string|null $createdAt
     * @param string|null $updatedAt
     */
    public function __construct(
        string $uuid,
        string $userId,
        string $surveyId,
        string $summary,
        ?string $createdAt = null,
        ?string $updatedAt = null
    ) {
        parent::__construct($uuid, $createdAt, $updatedAt);
        $this->userId   = $userId;
        $this->surveyId = $surveyId;
        $this->summary  = $summary;
    }

    public static function build(array $data, bool $buildByAlias = false): self
    {
        if ($buildByAlias) {
            $data = self::buildByAlias('insight_', $data);
        }
        return new self(
            $data['uuid'],
            $data['userId'],
            $data['surveyId'],
            $data['summary'],
            $data['createdAt'] ?? null,
            $data['updatedAt'] ?? null
        );
    }

    public function jsonSerialize()
    {
        return [
            'uuid'      => $this->uuid,
            'userId'    => $this->userId,
            'surveyId'  => $this->surveyId,
            'summary'   => $this->summary,
            'createdAt' => $this->createdAt,
            'updatedAt' => $this->updatedAt,
        ];
    }
}
