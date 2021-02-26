<?php

namespace Survey54\Reap\Framework\Validator\Respondent;

use Psr\Http\Message\ServerRequestInterface;
use Respect\Validation\Validator as v;
use Survey54\Library\Validation\Validator;

class PostCheckMobileValidator extends Validator
{
    /**
     * @param ServerRequestInterface $request
     * @return array
     */
    public function validate(ServerRequestInterface $request): array
    {
        $rules = [
            'mobile' => [v::stringType(), self::REQUIRED_TEXT],
        ];

        $data = $this->validateRequest($request, $rules);

        $this->validateMobile($data['mobile']);

        return $data;
    }
}
