<?php

namespace Survey54\Reap\Framework\Validator\AppReview;

use Psr\Http\Message\ServerRequestInterface;
use Respect\Validation\Validator as v;
use Survey54\Library\Validation\Validator;

class PutAppReviewValidator extends Validator
{
    /**
     * @param ServerRequestInterface $request
     * @return array
     */
    public function validate(ServerRequestInterface $request): array
    {
        $rules = [
            'dontShow' => [v::boolType(), 'should be boolean'],
        ];

        return $this->validateRequest($request, $rules);
    }
}
