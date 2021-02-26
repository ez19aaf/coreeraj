<?php

namespace Tests\Unit\Framework\Validator\Survey;

use Survey54\Reap\Framework\Validator\Survey\GetSurveysValidator;
use Tests\Unit\AbstractTestCase;

class GetSurveysValidatorTest extends AbstractTestCase
{
    protected GetSurveysValidator $getSurveysValidator;

    protected function setUp(): void
    {
        parent::setUp();

        $this->getSurveysValidator = new GetSurveysValidator();
    }

    public function testValidate(): void
    {
        $data = [
            'respondentId' => '38c5ca80-36bf-4c09-a349-c239b5f28202',
        ];

        $this->request->method('getQueryParams')
            ->willReturn($data);

        $actual = $this->getSurveysValidator->validate($this->request);

        self::assertEquals($data, $actual);
    }
}
