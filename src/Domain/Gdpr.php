<?php

namespace Survey54\Reap\Domain;

use Survey54\Library\Domain\Domain;

class Gdpr extends Domain
{
    public string $userId;
    public string $userType;
    public string $action;
    public int $duration; // is in days

    /**
     * Gdpr constructor.
     * @param string $uuid
     * @param string $userId
     * @param string $userType
     * @param string $action
     * @param int $duration
     * @param string|null $createdAt
     * @param string|null $updatedAt
     */
    public function __construct(
        string $uuid,
        string $userId,
        string $userType,
        string $action,
        int $duration = 0,
        ?string $createdAt = null,
        ?string $updatedAt = null
    ) {
        parent::__construct($uuid, $createdAt, $updatedAt);
        $this->userId   = $userId;
        $this->userType = $userType;
        $this->action   = $action;
        $this->duration = $duration;
    }

    public static function build(array $data, bool $buildByAlias = false): self
    {
        if ($buildByAlias) {
            $data = self::buildByAlias('gdpr_', $data);
        }
        return new self(
            $data['uuid'],
            $data['userId'],
            $data['userType'],
            $data['action'],
            $data['duration'],
            $data['createdAt'] ?? null,
            $data['updatedAt'] ?? null
        );
    }

    public function jsonSerialize()
    {
        return [
            'uuid'      => $this->uuid,
            'userId'    => $this->userId,
            'userType'  => $this->userType,
            'action'    => $this->action,
            'duration'  => $this->duration,
            'createdAt' => $this->createdAt,
            'updatedAt' => $this->updatedAt,
        ];
    }
}
