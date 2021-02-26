<?php

namespace Survey54\Reap\Domain;

use Survey54\Library\Domain\Domain;

class Ghost extends Domain
{
    public string $mobile;
    public string $ghostMobile;
    public string $organisationId;

    public function __construct(
        string $uuid,
        string $mobile,
        string $ghostMobile,
        string $organisationId,
        ?string $createdAt = null,
        ?string $updatedAt = null
    ) {
        parent::__construct($uuid, $createdAt, $updatedAt);
        $this->mobile         = $mobile;
        $this->ghostMobile    = $ghostMobile;
        $this->organisationId = $organisationId;
    }

    public static function build(array $data, bool $buildByAlias = false): self
    {
        if ($buildByAlias) {
            $data = self::buildByAlias('ghost_', $data);
        }
        return new self(
            $data['uuid'],
            $data['mobile'],
            $data['ghostMobile'],
            $data['organisationId'],
            $data['createdAt'] ?? null,
            $data['updatedAt'] ?? null
        );
    }

    public function jsonSerialize()
    {
        return [
            'uuid'           => $this->uuid,
            'mobile'         => $this->mobile,
            'ghostMobile'    => $this->ghostMobile,
            'organisationId' => $this->organisationId,
            'createdAt'      => $this->createdAt,
            'updatedAt'      => $this->updatedAt,
        ];
    }
}
