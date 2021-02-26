<?php

namespace Survey54\Reap\Domain\Objects;

use JsonSerializable;

class Scale implements JsonSerializable
{
    public int $to;
    public int $from;
    public string $toName;
    public string $fromName;

    /**
     * Scale constructor.
     * @param int $to
     * @param int $from
     * @param string $toName
     * @param string $fromName
     */
    public function __construct(int $to, int $from, string $toName, string $fromName)
    {
        $this->to       = $to;
        $this->from     = $from;
        $this->toName   = $toName;
        $this->fromName = $fromName;
    }

    public static function build(array $data): self
    {
        return new self(
            $data['to'],
            $data['from'],
            $data['toName'],
            $data['fromName'],
        );
    }

    public function toArray(): array
    {
        return json_decode(json_encode($this, JSON_THROW_ON_ERROR), true, 512, JSON_THROW_ON_ERROR);
    }

    public function jsonSerialize()
    {
        return [
            'to'       => $this->to,
            'from'     => $this->from,
            'toName'   => $this->toName,
            'fromName' => $this->fromName,
        ];
    }
}
