<?php

namespace Tests\Unit\Framework\Controller\Respondent;

use Slim\Http\Response;
use Survey54\Reap\Framework\Controller\Respondent\ChangePassword;
use Survey54\Reap\Framework\Validator\Respondent\ChangePasswordValidator;
use Tests\Unit\AbstractTestCase;

class ChangePasswordTest extends AbstractTestCase
{
    protected ChangePassword $changePassword;
    protected ChangePasswordValidator $changePasswordValidator;

    protected function setUp(): void
    {
        parent::setUp();

        $this->changePasswordValidator = $this->createMock(ChangePasswordValidator::class);
        $this->changePasswordValidator->method('validate')
            ->willReturn([
                'password'    => '123',
                'oldPassword' => null,
            ]);

        $this->changePassword = new ChangePassword($this->respondentService, $this->changePasswordValidator);
    }

    public function testResponse(): void
    {
        $this->request->method('getAttribute')
            ->willReturn('xx.xx.xxx');

        $response = $this->changePassword->execute($this->request);

        self::assertInstanceOf(Response::class, $response);
        self::assertEquals(202, $response->getStatusCode());
    }
}
