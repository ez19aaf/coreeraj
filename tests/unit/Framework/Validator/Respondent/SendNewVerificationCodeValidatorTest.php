<?php

namespace Tests\Unit\Framework\Validator\Respondent;

use Survey54\Library\Domain\Values\VerificationType;
use Survey54\Reap\Framework\Validator\Respondent\SendNewVerificationCodeValidator;
use Tests\Unit\AbstractTestCase;

class SendNewVerificationCodeValidatorTest extends AbstractTestCase
{
    protected SendNewVerificationCodeValidator $sendNewVerificationCodeValidator;

    protected function setUp(): void
    {
        parent::setUp();

        $this->sendNewVerificationCodeValidator = new SendNewVerificationCodeValidator();
    }

    public function testValidate(): void
    {
        $data = [
            'type'                => self::$respondentData['verificationType'],
            'value'               => self::$respondentData['email'],
            'oldVerificationCode' => self::$respondentData['verificationCode'],
        ];

        $this->request->method('getParsedBody')
            ->willReturn($data);

        $actual = $this->sendNewVerificationCodeValidator->validate($this->request);

        self::assertEquals($data, $actual);
    }

    public function testValidate_withoutEmail(): void
    {
        $data = [
            'type'  => VerificationType::MOBILE,
            'value' => self::$respondentData['mobile'],
        ];

        $this->request->method('getParsedBody')
            ->willReturn($data);

        $actual = $this->sendNewVerificationCodeValidator->validate($this->request);

        self::assertEquals($data, $actual);
    }
}
