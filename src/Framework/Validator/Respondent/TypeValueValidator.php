<?php

namespace Survey54\Reap\Framework\Validator\Respondent;

use Psr\Http\Message\ServerRequestInterface;
use Respect\Validation\Validator as v;
use Survey54\Library\Domain\Values\VerificationType;
use Survey54\Library\Validation\Validator;

class TypeValueValidator extends Validator
{
    public const SUPPORTED_DEVICE_TYPE_TEXT = 'is required (only supported device type allowed).';

    /**
     * @param ServerRequestInterface $request
     * @return array
     */
    public function validate(ServerRequestInterface $request): array
    {
        $rules = [
            'type'  => [v::in(VerificationType::toArray()), self::SUPPORTED_DEVICE_TYPE_TEXT],
            'value' => [v::stringType(), self::REQUIRED_TEXT],
        ];

        return $this->validateRequest($request, $rules);
    }
}
