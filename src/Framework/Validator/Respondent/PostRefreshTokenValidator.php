<?php

namespace Survey54\Reap\Framework\Validator\Respondent;

use Psr\Http\Message\ServerRequestInterface;
use Respect\Validation\Validator as v;
use Survey54\Library\Validation\Validator;

class PostRefreshTokenValidator extends Validator
{
    /**
     * @param ServerRequestInterface $request
     * @return array
     */
    public function validate(ServerRequestInterface $request): array
    {
        $rules = [
            'refreshToken' => [v::notEmpty(), self::REQUIRED_TEXT],
            'expiredToken' => [v::notEmpty(), self::REQUIRED_TEXT],
        ];

        return $this->validateRequest($request, $rules);
    }
}
