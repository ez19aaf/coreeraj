<?php

namespace Survey54\Reap\Framework\Validator\Ghost;

use Psr\Http\Message\ServerRequestInterface;
use Respect\Validation\Validator as v;
use Survey54\Library\Domain\Values\Country;
use Survey54\Library\Validation\Validator;

class PostGhostValidator extends Validator
{
    /**
     * @param ServerRequestInterface $request
     * @return array
     */
    public function validate(ServerRequestInterface $request): array
    {
        $rules = [
            'organisationId'     => [v::uuid(4), self::UUID_TEXT],
            'ghostIntoCountries' => [v::arrayType()->each(v::in(Country::toArray())), self::SUPPORTED_COUNTRY_TEXT],
            'ghostMobile'        => [v::stringType(), self::REQUIRED_TEXT],
        ];

        return $this->validateRequest($request, $rules);
    }
}
