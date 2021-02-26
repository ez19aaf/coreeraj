<?php

namespace Survey54\Reap\Framework\Validator\Response;

use Psr\Http\Message\ServerRequestInterface;
use Respect\Validation\Validator as v;
use Survey54\Library\Domain\Values\AgeGroup;
use Survey54\Library\Domain\Values\Employment;
use Survey54\Library\Domain\Values\Gender;
use Survey54\Library\Domain\Values\LsmGroup;
use Survey54\Library\Domain\Values\Race;
use Survey54\Library\Validation\Validator;

class CrossTabsWtDemographicsValidator extends Validator
{
    /**
     * @param ServerRequestInterface $request
     * @return array
     */
    public function validate(ServerRequestInterface $request): array
    {
        $rules = [
            'source'      => [v::arrayType()
                                  ->key('ageGroup', v::arrayType()->each(v::in(AgeGroup::toArray())), false)
                                  ->key('employment', v::arrayType()->each(v::in(Employment::toArray())), false)
                                  ->key('gender', v::arrayType()->each(v::in(Gender::toArray())), false)
                                  ->key('lsmGroup', v::arrayType()->each(v::in(LsmGroup::toArray())), false)
                                  ->key('race', v::arrayType()->each(v::in(Race::toArray())), false)
                              , self::REQUIRED_TEXT],
            'compareQIDs' => [v::arrayType()->length(1), self::REQUIRED_TEXT],
        ];

        return $this->validateRequest($request, $rules);
    }
}
