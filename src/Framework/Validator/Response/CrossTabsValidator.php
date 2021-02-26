<?php

namespace Survey54\Reap\Framework\Validator\Response;

use Psr\Http\Message\ServerRequestInterface;
use Respect\Validation\Validator as v;
use Survey54\Library\Validation\Validator;

class CrossTabsValidator extends Validator
{
    /**
     * @param ServerRequestInterface $request
     * @return array
     */
    public function validate(ServerRequestInterface $request): array
    {
        $rules = [
            'source'      => [v::arrayType()
                                  ->key('questionId', v::intType())
                                  ->key('response', v::oneOf(v::intType(), v::stringType()))
                              , self::REQUIRED_TEXT],
            'compareQIDs' => [v::arrayType()->length(1), self::REQUIRED_TEXT],
        ];

        return $this->validateRequest($request, $rules);
    }
}
