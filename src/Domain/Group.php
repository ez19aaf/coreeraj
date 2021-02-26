<?php


namespace Survey54\Reap\Domain;

use Survey54\Library\Domain\Domain;

class Group extends Domain
{
    public string $userId;
    public string $groupName;
    public string $recurrence;
    public string $groupType;
    public ?array $audience;


    /**
     * Group constructor.
     * @param string $uuid
     * @param string $userId
     * @param string $groupName
     * @param string $recurrence
     * @param string $groupType
     * @param array|null $audience
     * @param string|null $createdAt
     * @param string|null $updatedAt
     */
    public function __construct(
        string $uuid,
        string $userId,
        string $groupName,
        string $recurrence,
        string $groupType,
        ?array $audience = null,
        ?string $createdAt = null,
        ?string $updatedAt = null
    ) {
        parent::__construct($uuid, $createdAt, $updatedAt);
        $this->userId       = $userId;
        $this->groupName    = $groupName;
        $this->recurrence   = $recurrence;
        $this->groupType    = $groupType;
        $this->audience     = $audience;
    }

    public static function build(array $data, bool $buildByAlias = false): self
    {
        if ($buildByAlias) {
            $data = self::buildByAlias('response_', $data);
        }

        return new self(
            $data['uuid'],
            $data['userId'],
            $data['groupName'],
            $data['recurrence'],
            $data['groupType'],
            $data['audience'] ?? null,
            $data['createdAt'] ?? null,
            $data['updatedAt'] ?? null
        );
    }

    public function jsonSerialize()
    {
        return [
            'uuid'       => $this->uuid,
            'userId'     => $this->userId,
            'groupName'  => $this->groupName,
            'recurrence' => $this->recurrence,
            'groupType'  => $this->groupType,
            'audience'   => $this->audience,
            'createdAt'  => $this->createdAt,
            'updatedAt'  => $this->updatedAt,
        ];
    }
}
