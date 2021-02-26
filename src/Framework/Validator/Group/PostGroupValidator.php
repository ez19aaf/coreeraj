<?php

namespace Survey54\Reap\Framework\Validator\Group;

use Psr\Http\Message\ServerRequestInterface;
use Respect\Validation\Validator as v;
use Survey54\Library\Domain\Values\Country;
use Survey54\Library\Domain\Values\GroupType;
use Survey54\Library\Validation\Validator;

class PostGroupValidator extends Validator
{
    /**
     * @param ServerRequestInterface $request
     * @return array
     */
    public function validate(ServerRequestInterface $request): array
    {
        $action = $request->getParsedBody()['groupType'] ?? null;
        $isSA  = ($country = $request->getParsedBody()['country'] ?? null) && $country === Country::SOUTH_AFRICA;
        $rules['userId']      = [v::uuid(4), self::UUID_TEXT];
        $rules['groupName']   = [v::stringType()->length(2), self::REQUIRED_TEXT];
        $rules['groupType']   = [v::stringType()->length(2), self::REQUIRED_TEXT];
        $rules['recurrence']  = [v::stringType()->length(2), self::REQUIRED_TEXT];

        switch ($action) {
            case GroupType::UPLOADED:
                $rules['audience']   = [v::arrayType()];
                break;
            case GroupType::DEMOGRAPHIC:
                $rules['country']    = [v::stringType()::notEmpty(), self::REQUIRED_TEXT, $isSA];
                $rules['sample']     = [v::arrayType()
                    ->key('lsmGroup', v::arrayType(), self::REQUIRED_TEXT)
                    ->key('race', v::arrayType(), self::SUPPORTED_RACE_TEXT)
                    ->key('ageGroup', v::arrayType()->length(1, 20)->each(v::stringType()), self::SUPPORTED_AGE_GROUP_TEXT)
                    ->key('gender', v::arrayType()->length(1, 20)->each(v::stringType()), self::SUPPORTED_GENDER_TEXT)
                    ->key('employment', v::arrayType()->length(1, 20)->each(v::stringType()), self::SUPPORTED_EMPLOYMENT_TEXT)
                ];
                $rules['quantity']   = [v::intType()->positive()->min(1), 'quantity will not be empty'];
                break;
        }
        return $this->validateRequest($request, $rules);
    }
}
