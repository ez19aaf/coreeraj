<?php

namespace Tests\Unit\Framework\Validator\Respondent;

use Survey54\Reap\Framework\Validator\Respondent\ChangePasswordValidator;
use Tests\Unit\AbstractTestCase;

class ChangePasswordValidatorTest extends AbstractTestCase
{
    protected ChangePasswordValidator $changePasswordValidator;

    protected function setUp(): void
    {
        parent::setUp();

        $this->changePasswordValidator = new ChangePasswordValidator();
    }

    public function testValidate(): void
    {
        $data = [
            'password'    => self::$respondentData['password'],
            'oldPassword' => self::$respondentData['password'],
        ];

        $this->request->method('getParsedBody')
            ->willReturn($data);

        $actual = $this->changePasswordValidator->validate($this->request);

        self::assertEquals($data, $actual);
    }
}
