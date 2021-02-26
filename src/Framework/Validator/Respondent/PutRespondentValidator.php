<?php

namespace Survey54\Reap\Framework\Validator\Respondent;

use Psr\Http\Message\ServerRequestInterface;
use Respect\Validation\Validator as v;
use Survey54\Library\Domain\Values\Employment;
use Survey54\Library\Domain\Values\Gender;
use Survey54\Library\Domain\Values\Race;
use Survey54\Library\Validation\Validator;
use Survey54\Reap\Framework\Exception\Error;

class PutRespondentValidator extends Validator
{
    public const SUPPORTED_AGE_GROUP_TEXT  = 'is required (only supported age group allowed).';
    public const SUPPORTED_EMPLOYMENT_TEXT = 'is required (only supported employment allowed).';
    public const SUPPORTED_GENDER_TEXT     = 'is required (only supported gender allowed).';
    public const SUPPORTED_RACE_TEXT       = 'is required (only supported race allowed).';

    /**
     * @param ServerRequestInterface $request
     * @return array
     */
    public function validate(ServerRequestInterface $request): array
    {
        $rules = [
            'firstName'   => [v::oneOf(v::nullType(), v::alpha('-')->length(2, 20)), self::ALPHA_HYPHEN_TEXT, false],
            'lastName'    => [v::oneOf(v::nullType(), v::alpha('-')->length(2, 20)), self::ALPHA_HYPHEN_TEXT, false],
            'email'       => [v::oneOf(v::nullType(), v::email()), self::REQUIRED_TEXT, false],
            'dateOfBirth' => [v::date('d-m-Y'), self::REQUIRED_TEXT, false],
            'employment'  => [v::in(Employment::toArray()), self::SUPPORTED_EMPLOYMENT_TEXT],
            'gender'      => [v::in(Gender::toArray()), self::SUPPORTED_GENDER_TEXT],
            'race'        => [v::in(Race::toArray()), self::SUPPORTED_RACE_TEXT, false],
            'region'      => [v::oneOf(v::nullType(), v::stringType()->length(2)), self::REQUIRED_TEXT, false],
        ];
        $email = filter_var($params['email'] ?? '', FILTER_SANITIZE_EMAIL);
        if ($email) {
            $validEmail = filter_var($email, FILTER_VALIDATE_EMAIL);
            if (!$validEmail) {
                Error::throwError(Error::S542_INVALID_EMAIL_ADDRESS);
            }
        }

        $data = $this->validateRequest($request, $rules);

        if (isset($data['dateOfBirth'])) {
            $this->validateDOB($data['dateOfBirth']);
        }

        return $data;
    }
}
