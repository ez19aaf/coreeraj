<?php

namespace Tests\Unit\Framework\Validator\Respondent;

use Survey54\Reap\Framework\Validator\Respondent\PutProfileImageValidator;
use Tests\Unit\AbstractTestCase;

class PutProfileImageValidatorTest extends AbstractTestCase
{
    protected PutProfileImageValidator $putProfileImageValidator;

    protected function setUp(): void
    {
        parent::setUp();

        $this->putProfileImageValidator = new PutProfileImageValidator();
    }

    public function testValidate(): void
    {
        $data = [
            'image' => ['https://cdn.pixabay.com/photo/2016/07/10/19/19/interior-design-1508276_960_720.jpg'],
        ];

        $this->request->method('getParsedBody')
            ->willReturn($data);

        $actual = $this->putProfileImageValidator->validate($this->request);

        self::assertEquals($data, $actual);
    }
}
