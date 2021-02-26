<?php

namespace Survey54\Reap\Framework\Validator\Survey;

use Psr\Http\Message\ServerRequestInterface;
use Respect\Validation\Validator as v;
use Survey54\Library\Validation\Validator;

class PostLaunchSmsUssdAudValidator extends Validator
{
    /**
     * @param ServerRequestInterface $request
     * @return array
     */
    public function validate(ServerRequestInterface $request): array
    {
        $rules = [
            'smsCode'  => [v::stringType(), 'should be a string.'],
            'ussdCode' => [v::oneOf(v::nullType(), v::stringType()), 'should be null or a string.'],
        ];

        return $this->validateRequest($request, $rules);
    }
}
