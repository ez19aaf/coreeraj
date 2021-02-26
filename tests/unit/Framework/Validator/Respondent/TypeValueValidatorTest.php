<?php

namespace Tests\Unit\Framework\Validator\Respondent;

use Survey54\Reap\Framework\Validator\Respondent\TypeValueValidator;
use Tests\Unit\AbstractTestCase;

class TypeValueValidatorTest extends AbstractTestCase
{
    protected TypeValueValidator $typeValueValidator;

    protected function setUp(): void
    {
        parent::setUp();

        $this->typeValueValidator = new TypeValueValidator();
    }

    public function testValidate(): void
    {
        $data = [
            'type'  => self::$respondentData['verificationType'],
            'value' => self::$respondentData['email'],
        ];

        $this->request->method('getParsedBody')
            ->willReturn($data);

        $actual = $this->typeValueValidator->validate($this->request);

        self::assertEquals($data, $actual);
    }
}
