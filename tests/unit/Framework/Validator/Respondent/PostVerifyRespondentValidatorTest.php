<?php

namespace Tests\Unit\Framework\Validator\Respondent;

use Survey54\Reap\Framework\Validator\Respondent\PostVerifyRespondentValidator;
use Tests\Unit\AbstractTestCase;

class PostVerifyRespondentValidatorTest extends AbstractTestCase
{
    protected PostVerifyRespondentValidator $postVerifyRespondentValidator;

    protected function setUp(): void
    {
        parent::setUp();

        $this->postVerifyRespondentValidator = new PostVerifyRespondentValidator();
    }

    public function testValidate(): void
    {
        $data = [
            'type'             => self::$respondentData['verificationType'],
            'value'            => self::$respondentData['mobile'],
            'verificationCode' => self::$respondentData['verificationCode'],
        ];

        $this->request->method('getParsedBody')
            ->willReturn($data);

        $actual = $this->postVerifyRespondentValidator->validate($this->request);

        self::assertEquals($data, $actual);
    }
}
