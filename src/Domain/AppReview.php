<?php

namespace Survey54\Reap\Domain;

use Survey54\Library\Domain\Domain;

class AppReview extends Domain
{
    public int $rate;
    public bool $dontShow;

    /**
     * AppReview constructor.
     * @param string $uuid
     * @param int $rate
     * @param bool $dontShow
     * @param string|null $createdAt
     * @param string|null $updatedAt
     */
    public function __construct(
        string $uuid,
        bool $dontShow,
        ?string $createdAt = null,
        ?string $updatedAt = null
    ) {
        parent::__construct($uuid, $createdAt, $updatedAt);
        $this->dontShow = $dontShow;
    }

    public static function build(array $data, bool $buildByAlias = false): self
    {
        if ($buildByAlias) {
            $data = self::buildByAlias('app_review', $data);
        }

        return new self(
            $data['uuid'],
            $data['dontShow'],
            $data['createdAt'] ?? null,
            $data['updatedAt'] ?? null
        );
    }

    public function jsonSerialize()
    {
        return [
            'uuid'      => $this->uuid,
            'dontShow'  => $this->dontShow,
            'createdAt' => $this->createdAt,
            'updatedAt' => $this->updatedAt,
        ];
    }
}
