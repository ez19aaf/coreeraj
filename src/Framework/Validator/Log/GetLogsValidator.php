<?php

namespace Survey54\Reap\Framework\Validator\Log;

use Psr\Http\Message\ServerRequestInterface;
use Respect\Validation\Validator as v;
use Survey54\Library\Validation\Validator;

class GetLogsValidator extends Validator
{
    /**
     * @param ServerRequestInterface $request
     * @return array
     */
    public function validate(ServerRequestInterface $request): array
    {
        $rules = [
            'objectId'   => [v::uuid(4), self::UUID_TEXT],
            'objectType' => [v::stringType(), self::REQUIRED_TEXT],
            'action'     => [v::stringType(), self::REQUIRED_TEXT],
            'page'       => [v::intVal()->positive(), self::NON_ZERO_NUMBER_TEXT, false],
            'limit'      => [v::intVal()->positive(), self::NON_ZERO_NUMBER_TEXT, false],
        ];

        return $this->validateRequest($request, $rules, true);
    }
}
