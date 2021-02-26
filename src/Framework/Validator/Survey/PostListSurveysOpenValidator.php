<?php

namespace Survey54\Reap\Framework\Validator\Survey;

use Psr\Http\Message\ServerRequestInterface;
use Respect\Validation\Validator as v;
use Survey54\Library\Domain\Values\Country;
use Survey54\Library\Domain\Values\Employment;
use Survey54\Library\Domain\Values\Gender;
use Survey54\Library\Domain\Values\Race;
use Survey54\Library\Validation\Validator;

class PostListSurveysOpenValidator extends Validator
{
    /**
     * @param ServerRequestInterface $request
     * @return array
     */
    public function validate(ServerRequestInterface $request): array
    {
        // if respondentId is passed then don't require others
        if (($rId = $request->getParsedBody()['respondentId'] ?? null) && strlen($rId) === 36) {
            $rules = [
                'respondentId' => [v::oneOf(v::nullType(), v::uuid(4)), self::REQUIRED_TEXT],
            ];
        } else {
            $isSA  = ($country = $request->getParsedBody()['country'] ?? null) && $country === Country::SOUTH_AFRICA;
            $rules = [
                'country'     => [v::in(Country::toArray()), self::SUPPORTED_COUNTRY_TEXT],
                'mobile'      => [v::stringType(), self::REQUIRED_TEXT],
                'dateOfBirth' => [v::date('d-m-Y'), self::REQUIRED_TEXT],
                'employment'  => [v::in(Employment::toArray()), self::SUPPORTED_EMPLOYMENT_TEXT],
                'gender'      => [v::in(Gender::toArray()), self::SUPPORTED_GENDER_TEXT],
                'race'        => [v::in(Race::toArray()), self::SUPPORTED_RACE_TEXT, $isSA],
                'lsmKeys'     => [v::arrayType(), self::REQUIRED_TEXT, $isSA],
            ];
        }

        $data = $this->validateRequest($request, $rules);

        if (isset($data['mobile'])) {
            $this->validateMobile($data['mobile'], $data['country']);
        }
        if (isset($data['dateOfBirth'])) {
            $this->validateDOB($data['dateOfBirth']);
        }

        return $data;
    }
}
