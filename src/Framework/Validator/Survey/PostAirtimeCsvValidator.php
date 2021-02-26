<?php


namespace Survey54\Reap\Framework\Validator\Survey;

use Psr\Http\Message\ServerRequestInterface;
use Respect\Validation\Validator as v;
use Survey54\Library\Domain\Values\Country;
use Survey54\Library\Exception\ExtendedException;
use Survey54\Library\Validation\Validator;
use Survey54\Reap\Framework\Exception\Error;

class PostAirtimeCsvValidator extends Validator
{

    /**
     * @param ServerRequestInterface $request
     * @return array
     */
    public function validate(ServerRequestInterface $request): array
    {
        $rules  = [
            'incentive' => [v::intType()->positive()->min(1), self::REQUIRED_TEXT],
            'country'   => [v::stringType()::notEmpty(), self::REQUIRED_TEXT],
            'numbers'   => [v::arrayType()->notEmpty()->unique()->each(v::phone()), 'is required and must be unique.'],
        ];
        $result = $this->validateRequest($request, $rules);

        $mobiles = $result['numbers'];
        $country = $result['country'];

        $mobileErrors = [
            Country::KENYA        => Error::S54_KENYA_MOBILE,
            Country::SOUTH_AFRICA => Error::S54_SOUTH_AFRICA_MOBILE,
            Country::NIGERIA      => Error::S54_NIGERIA_MOBILE,
            Country::GHANA        => Error::S54_GHANA_MOBILE,
        ];

        foreach ($mobiles as $mobile) {
            try {
                $this->validateMobile($mobile, $country);
            } catch (ExtendedException $e) {
                $error            = $mobileErrors[$country] ?? Error::S54_MOBILE_COUNTRY_MISMATCH;
                $error['message'] = str_replace('mobile number', "mobile number {$mobile}", $error['message']);
                Error::throwError($error, $e->getDeveloperMessage());
            }
        }
        return $result;
    }
}
