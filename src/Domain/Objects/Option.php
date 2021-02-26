<?php

namespace Survey54\Reap\Domain\Objects;

use JsonSerializable;

class Option implements JsonSerializable
{
    public int $id;
    public string $text;
    public ?int $goto;
    public bool $other;

    /**
     * Option constructor.
     * @param int $id
     * @param string $text
     * @param int|null $goto
     * @param bool $other
     */
    public function __construct(int $id, string $text, ?int $goto = null, bool $other = false)
    {
        $this->id    = $id;
        $this->text  = $text;
        $this->goto  = $goto;
        $this->other = $other;
    }

    public static function build(array $data): self
    {
        return new self(
            $data['id'],
            $data['text'],
            $data['goto'] ?? null,
            $data['other'] ?? false,
        );
    }

    public function toArray(): array
    {
        return json_decode(json_encode($this, JSON_THROW_ON_ERROR), true, 512, JSON_THROW_ON_ERROR);
    }

    public function jsonSerialize()
    {
        return [
            'id'    => $this->id,
            'text'  => $this->text,
            'goto'  => $this->goto,
            'other' => $this->other,
        ];
    }
}
