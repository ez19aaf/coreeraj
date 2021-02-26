<?php

namespace Survey54\Reap\Framework\Validator\Respondent;

use Psr\Http\Message\ServerRequestInterface;
use Respect\Validation\Validator as v;
use Survey54\Library\Domain\Values\Country;
use Survey54\Library\Validation\Validator;

class SendTextValidator extends Validator
{
    /**
     * @param ServerRequestInterface $request
     * @return array
     */
    public function validate(ServerRequestInterface $request): array
    {
        $rules = [
            'country' => [v::in(Country::toArray()), self::SUPPORTED_COUNTRY_TEXT],
            'text'    => [v::stringType(), self::REQUIRED_TEXT],
            'limit'   => [v::intType()->positive(), self::NON_ZERO_NUMBER_TEXT],
        ];

        return $this->validateRequest($request, $rules);
    }
}
