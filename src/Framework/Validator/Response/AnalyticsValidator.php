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

class AnalyticsValidator extends Validator
{
    /**
     * @param ServerRequestInterface $request
     * @return array
     */
    public function validate(ServerRequestInterface $request): array
    {
        $rules = [
            'questionId'  => [v::arrayType()->length(1), self::REQUIRED_TEXT, false],
            'answerIds'   => [v::arrayType()->length(1), self::REQUIRED_TEXT, false],
            'answerRank'  => [v::arrayType()->length(1), self::REQUIRED_TEXT, false],
            'answerScale' => [v::arrayType()->length(1), self::REQUIRED_TEXT, false],
            'ageGroup'    => [v::arrayType()->each(v::in(AgeGroup::toArray())), self::REQUIRED_TEXT, false],
            'employment'  => [v::arrayType()->each(v::in(Employment::toArray())), self::REQUIRED_TEXT, false],
            'gender'      => [v::arrayType()->each(v::in(Gender::toArray())), self::REQUIRED_TEXT, false],
            'lsmGroup'    => [v::arrayType()->each(v::in(LsmGroup::toArray())), self::REQUIRED_TEXT, false],
            'race'        => [v::arrayType()->each(v::in(Race::toArray())), self::REQUIRED_TEXT, false],
        ];

        return $this->validateRequest($request, $rules);
    }
}
