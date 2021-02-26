<?php

namespace Survey54\Reap\Domain;

use Survey54\Library\Domain\Domain;

class AirtimeLogsCsv extends Domain
{
    public string $mobile;
    public ?bool $redeemed;
    public ?array $proof;
    public ?bool $errored;
    public ?array $error;

    /**
     * AirtimeLogsCsv constructor.
     * @param string $uuid
     * @param string $mobile
     * @param bool|null $redeemed
     * @param array|null $proof
     * @param bool|null $errored
     * @param array|null $error
     * @param string|null $createdAt
     * @param string|null $updatedAt
     */
    public function __construct(
        string $uuid,
        string $mobile,
        ?bool $redeemed,
        ?array $proof,
        ?bool $errored,
        ?array $error,
        ?string $createdAt = null,
        ?string $updatedAt = null
    ) {
        parent::__construct($uuid, $createdAt, $updatedAt);
        $this->mobile   = $mobile;
        $this->redeemed = $redeemed;
        $this->proof    = $proof;
        $this->errored  = $errored;
        $this->error    = $error;
    }

    public static function build(array $data, bool $buildByAlias = false): self
    {
        if ($buildByAlias) {
            $data = self::buildByAlias('airtime_logs_', $data);
        }

        return new self(
            $data['uuid'],
            $data['mobile'],
            $data['redeemed'] ?? false,
            $data['proof'] ?? null,
            $data['errored'] ?? false,
            $data['error'] ?? null,
            $data['createdAt'] ?? null,
            $data['updatedAt'] ?? null
        );
    }

    public function jsonSerialize()
    {
        return [
            'uuid'      => $this->uuid,
            'mobile'    => $this->mobile,
            'redeemed'  => $this->redeemed,
            'proof'     => $this->proof,
            'errored'   => $this->errored,
            'error'     => $this->error,
            'createdAt' => $this->createdAt,
            'updatedAt' => $this->updatedAt,
        ];
    }
}
