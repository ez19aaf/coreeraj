<?php


namespace Survey54\Reap\Framework\Validator\Group;

use Psr\Http\Message\ServerRequestInterface;
use Respect\Validation\Validator as v;
use Survey54\Library\Validation\Validator;

class GetGroupsValidator extends Validator
{
    /**
     * @param ServerRequestInterface $request
     * @return array
     */
    public function validate(ServerRequestInterface $request): array
    {
        $rules = [
            'userId'        => [v::uuid(4), self::UUID_TEXT, false],
            'page'          => [v::intVal()->positive(), self::NON_ZERO_NUMBER_TEXT, false],
            'limit'         => [v::intVal()->positive(), self::NON_ZERO_NUMBER_TEXT, false],
        ];

        return $this->validateRequest($request, $rules, true);
    }
}
