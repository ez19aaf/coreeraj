<?php

namespace Survey54\Reap\Framework\Validator\Response;

use Psr\Http\Message\ServerRequestInterface;
use Respect\Validation\Validator as v;
use Survey54\Library\Domain\Values\Employment;
use Survey54\Library\Domain\Values\Gender;
use Survey54\Library\Domain\Values\Race;
use Survey54\Library\Validation\Validator;

class PostResponseSetupValidator extends Validator
{
    private const USER_CONFIG_TEXT = 'must contain the following fields in the right format: mobile, ageGroup, gender, race.';

    /**
     * @param ServerRequestInterface $request
     * @return array
     */
    public function validate(ServerRequestInterface $request): array
    {
        /**
         * If metadata is updated here, also update it in ResponseService->create() fn
         */

        // if respondentId is passed then only require mobile in metadata
        $bool = true;
        if (($rId = $request->getParsedBody()['respondentId'] ?? null) && strlen($rId) === 36) {
            $bool = false;
        }

        $rules = [
            'surveyId'     => [v::notEmpty()->length(36, 36), self::REQUIRED_TEXT],
            'isCint'       => [v::boolType(), self::REQUIRED_TEXT],
            'respondentId' => [v::oneOf(v::nullType(), v::uuid(4)), self::REQUIRED_TEXT],
            'metadata'     => [v::arrayType()
                                   ->key('email', v::oneOf(v::nullType(), v::email()), false)
                                   ->key('country', v::stringType())
                                   ->key('mobile', v::stringType())
                                   ->key('ipAddress', v::oneOf(v::nullType(), v::notEmpty()), $bool)
                                   ->key('dateOfBirth', v::date('d-m-Y'), $bool)
                                   ->key('employment', v::oneOf(v::nullType(), v::in(Employment::toArray())), $bool)
                                   ->key('gender', v::in(Gender::toArray()), $bool)
                                   ->key('race', v::oneOf(v::nullType(), v::in(Race::toArray())), false)
                                   ->key('lsmKeys', v::arrayType(), false)
                               , self::USER_CONFIG_TEXT],
        ];

        $data = $this->validateRequest($request, $rules);

        $this->validateMobile($data['metadata']['mobile'], $data['metadata']['country']);

        if (isset($data['metadata']['dateOfBirth'])) {
            $this->validateDOB($data['metadata']['dateOfBirth']);
        }

        return $data;
    }
}
