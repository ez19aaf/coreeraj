<?php

namespace Survey54\Reap\Framework\Validator\Response;

use Psr\Http\Message\ServerRequestInterface;
use Respect\Validation\Validator as v;
use Survey54\Library\Validation\Validator;

class PostResponseValidator extends Validator
{
    private const NULL_OR_REQUIRED_TEXT     = 'must be null or not empty.';
    private const NULL_OR_POSITIVE_INT_TEXT = 'must be null or a positive integer.';
    private const POSITIVE_INT_TEXT         = 'must be a positive integer.';

    /**
     * @param ServerRequestInterface $request
     * @return array
     */
    public function validate(ServerRequestInterface $request): array
    {
        // Array key-val https://respect-validation.readthedocs.io/en/1.1/rules/Key/#key
        $rules = [
            'respondentId' => [v::notEmpty(), self::REQUIRED_TEXT],
            'surveyId'     => [v::uuid(4), self::UUID_TEXT],
            'questionId'   => [v::positive(), self::POSITIVE_INT_TEXT],
            'answer'       => [v::oneOf(v::nullType(), v::notEmpty()), self::NULL_OR_REQUIRED_TEXT],
            'answerIds'    => [v::oneOf(v::nullType(), v::notEmpty()->arrayType()), self::NULL_OR_REQUIRED_TEXT],
            'answerRank'   => [v::oneOf(v::nullType(), v::intType()), self::NULL_OR_POSITIVE_INT_TEXT, false],
            'answerScale'  => [v::oneOf(v::nullType(), v::intType()), self::NULL_OR_POSITIVE_INT_TEXT, false],
        ];

        return $this->validateRequest($request, $rules);
    }
}
