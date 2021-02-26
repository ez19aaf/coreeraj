<?php

namespace Survey54\Reap\Domain\Objects;

use JsonSerializable;
use Survey54\Library\Domain\Values\QuestionType;

class Question implements JsonSerializable
{
    public int $id;
    public string $type;
    public string $text;
    public ?Scale $scale = null;
    public ?int $goto = null;
    public bool $withOther = false;
    public bool $withReason = false;
    public ?string $boundTime = null;
    public ?string $boundDate = null;
    public bool $repeatOnOptions = false;
    public ?string $repeatTemplate = null;
    public ?array $options = null;

    /**
     * Question constructor.
     * @param int $id
     * @param string $type
     * @param string $text
     */
    public function __construct(
        int $id,
        string $type,
        string $text
    ) {
        $this->id   = $id;
        $this->type = $type;
        $this->text = $text;
    }

    public static function build(array $data): self
    {
        $validScale                = isset($data['scale']) && is_array($data['scale']);
        $question                  = new self(
            $data['id'],
            $data['type'],
            $data['text'],
        );
        $question->scale           = $validScale ? Scale::build($data['scale']) : null;
        $question->goto            = $data['goto'] ?? null;
        $question->withOther       = $data['withOther'] ?? false;
        $question->withReason      = $data['withReason'] ?? false;
        $question->boundTime       = $data['boundTime'] ?? null;
        $question->boundDate       = $data['boundDate'] ?? null;
        $question->repeatOnOptions = $data['repeatOnOptions'] ?? false;
        $question->repeatTemplate  = $data['repeatTemplate'] ?? null;
        $question->options         = $data['options'] ?? null;

        return $question;
    }

    public function toArray(): array
    {
        return json_decode(json_encode($this, JSON_THROW_ON_ERROR), true, 512, JSON_THROW_ON_ERROR);
    }

    public function jsonSerialize()
    {
        $data = [
            'id'              => $this->id,
            'type'            => $this->type,
            'text'            => $this->text,
            'options'         => $this->options,
            'scale'           => $this->scale,
            'goto'            => $this->goto,
            'withOther'       => $this->withOther,
            'withReason'      => $this->withReason,
            // AM Set
            'boundTime'       => $this->boundTime,
            'boundDate'       => $this->boundDate,
            'repeatOnOptions' => $this->repeatOnOptions,
            'repeatTemplate'  => $this->repeatTemplate,
        ];
        if ($this->type === QuestionType::OPEN_ENDED) {
            $data['goto'] = null;
        }
        return $data;
    }

    /**
     * For AM Question
     * @param array $data
     * @return array
     */
    public static function expandTemplate(array $data): array
    {
        foreach ($data['questions'] as $questionData) {
            $question = self::build($questionData);

            if ($question->repeatOnOptions) {
                foreach ($question->options as $optionData) {
                    $option = Option::build($optionData);

                    $q                   = new Question(
                        ++$question->id,
                        QuestionType::OPEN_ENDED,
                        str_replace("{{optionText}}", $option->text, $question->repeatTemplate),
                    );
                    $q->boundTime        = $option->text;
                    $q->boundDate        = $question->boundDate;
                    $data['questions'][] = $q->toArray();
                }
            }
        }

        // order questions by id
        $keys = array_column($data['questions'], 'id');
        array_multisort($keys, SORT_ASC, $data['questions']);

        return $data;
    }
}
