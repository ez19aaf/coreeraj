<?php

namespace Tests\Unit\Framework\Validator\Survey;


use Survey54\Reap\Framework\Validator\Survey\PostLaunchSmsUssdAudValidator;
use Tests\Unit\AbstractTestCase;

class PostLaunchSmsUssdValidatorTest extends AbstractTestCase
{
    protected PostLaunchSmsUssdAudValidator $postLaunchSmsUSSDValidator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->postLaunchSmsUSSDValidator = new PostLaunchSmsUssdAudValidator();
    }

    public function testWillReturnData(): void
    {
        $data = [
            'smsCode'  => '24324',
            'ussdCode' => '*122*12*2*2#',
        ];
        $this->request->method('getParsedBody')
            ->willReturn($data);
        $actual = $this->postLaunchSmsUSSDValidator->validate($this->request);
        self::assertEquals($actual, $data);
    }
}
