<?php

namespace Survey54\Reap\Framework\Validator\Insight;

use Psr\Http\Message\ServerRequestInterface;
use Respect\Validation\Validator as v;
use Survey54\Library\Validation\Validator;

class PostInsightsValidator extends Validator
{
    private const SUMMARY_TEXT = 'must contain 1 to 15 insights.';

    /**
     * @param ServerRequestInterface $request
     * @return array
     */
    public function validate(ServerRequestInterface $request): array
    {
        $rules = [
            'userId'   => [v::uuid(4), self::UUID_TEXT],
            'surveyId' => [v::uuid(4), self::UUID_TEXT],
            'summary'  => [v::arrayType()->length(1, 20)->each(v::stringType()), self::SUMMARY_TEXT],
        ];

        return $this->validateRequest($request, $rules);
    }
}
