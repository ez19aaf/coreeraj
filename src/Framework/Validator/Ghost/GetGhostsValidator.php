<?php

namespace Survey54\Reap\Framework\Validator\Ghost;

use Psr\Http\Message\ServerRequestInterface;
use Respect\Validation\Validator as v;
use Survey54\Library\Validation\Validator;

class GetGhostsValidator extends Validator
{
    /**
     * @param ServerRequestInterface $request
     * @return array
     */
    public function validate(ServerRequestInterface $request): array
    {
        $rules = [
            'organisationId' => [v::uuid(4), self::UUID_TEXT, false],
            'page'           => [v::intVal()->positive(), self::NON_ZERO_NUMBER_TEXT, false],
            'limit'          => [v::intVal()->positive(), self::NON_ZERO_NUMBER_TEXT, false],
        ];

        return $this->validateRequest($request, $rules, true);
    }
}
