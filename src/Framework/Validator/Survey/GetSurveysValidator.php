<?php

namespace Survey54\Reap\Framework\Validator\Survey;

use Psr\Http\Message\ServerRequestInterface;
use Respect\Validation\Validator as v;
use Survey54\Library\Validation\Validator;

class GetSurveysValidator extends Validator
{
    /**
     * @param ServerRequestInterface $request
     * @return array
     */
    public function validate(ServerRequestInterface $request): array
    {
        $rules = [
            'respondentId' => [v::uuid(4), self::UUID_TEXT],
            'history'      => [v::boolVal(), 'should be a true or false.', false],
        ];

        return $this->validateRequest($request, $rules, true);
    }
}
