<?php

namespace Survey54\Reap\Framework\Validator\Respondent;

use Psr\Http\Message\ServerRequestInterface;
use Respect\Validation\Validator as v;
use Survey54\Library\Domain\Values\Country;
use Survey54\Library\Domain\Values\Employment;
use Survey54\Library\Domain\Values\Gender;
use Survey54\Library\Domain\Values\Race;
use Survey54\Library\Domain\Values\SignedUpSource;
use Survey54\Library\Validation\Validator;
use Survey54\Reap\Framework\Exception\Error;

class PostRespondentValidator extends Validator
{
    /**
     * @param ServerRequestInterface $request
     * @return array
     */
    public function validate(ServerRequestInterface $request): array
    {
        //password is in the Auth header
        $rules = [
            'firstName'      => [v::oneOf(v::nullType(), v::alpha('-')->length(2, 20)), self::ALPHA_HYPHEN_TEXT, false],
            'lastName'       => [v::oneOf(v::nullType(), v::alpha('-')->length(2, 20)), self::ALPHA_HYPHEN_TEXT, false],
            'email'          => [v::email(), self::SUPPORTED_EMAIL_TEXT, false],
            'mobile'         => [v::stringType(), self::REQUIRED_TEXT],
            'dateOfBirth'    => [v::date('d-m-Y'), self::REQUIRED_TEXT, false],
            'employment'     => [v::in(Employment::toArray()), self::SUPPORTED_EMPLOYMENT_TEXT, false],
            'gender'         => [v::in(Gender::toArray()), self::SUPPORTED_GENDER_TEXT, false],
            'race'           => [v::in(Race::toArray()), self::SUPPORTED_RACE_TEXT, false],
            'country'        => [v::in(Country::toArray()), self::SUPPORTED_COUNTRY_TEXT],
            'region'         => [v::oneOf(v::nullType(), v::stringType()->length(2)), self::REQUIRED_TEXT, false],
            'signedUpSource' => [v::oneOf(v::nullType(), v::in(SignedUpSource::toArray())), self::SUPPORTED_SIGNED_UP_SOURCE, false],
        ];

        $data = $this->validateRequest($request, $rules);

        $this->validateMobile($data['mobile'], $data['country']);

        $email = filter_var($params['email'] ?? '', FILTER_SANITIZE_EMAIL);
        if ($email) {
            $validEmail = filter_var($email, FILTER_VALIDATE_EMAIL);
            if (!$validEmail) {
                Error::throwError(Error::S542_INVALID_EMAIL_ADDRESS);
            }
        }

        if (isset($data['dateOfBirth'])) {
            $this->validateDOB($data['dateOfBirth']);
        }

        return $data;
    }
}
