<?php

namespace Survey54\Reap\Framework\Validator\Insight;

use Psr\Http\Message\ServerRequestInterface;
use Respect\Validation\Validator as v;
use Survey54\Library\Validation\Validator;

class GetInsightsValidator extends Validator
{
    /**
     * @param ServerRequestInterface $request
     * @return array
     */
    public function validate(ServerRequestInterface $request): array
    {
        $rules = [
            'surveyId' => [v::uuid(4), self::UUID_TEXT, false],
            'page'     => [v::intVal()->positive(), self::NON_ZERO_NUMBER_TEXT, false],
            'limit'    => [v::intVal()->positive(), self::NON_ZERO_NUMBER_TEXT, false],
        ];

        return $this->validateRequest($request, $rules, true);
    }
}
