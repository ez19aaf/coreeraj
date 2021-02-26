<?php

namespace Survey54\Reap\Framework\Validator\Respondent;

use Psr\Http\Message\ServerRequestInterface;
use Respect\Validation\Validator as v;
use Survey54\Library\Validation\Validator;

class ChangePasswordValidator extends Validator
{
    /**
     * @param ServerRequestInterface $request
     * @return array
     */
    public function validate(ServerRequestInterface $request): array
    {
        $rules = [
            'password'    => [v::notEmpty()->regex(self::PASSWORD_REGEX), self::PASSWORD_TEXT],
            'oldPassword' => [v::notEmpty()->regex(self::PASSWORD_REGEX), self::PASSWORD_TEXT],
        ];

        return $this->validateRequest($request, $rules);
    }
}
