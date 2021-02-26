<?php

namespace Tests\Unit\Framework\Controller\Respondent;

use Exception;
use Slim\Http\Response;
use Survey54\Reap\Framework\Controller\Respondent\SetPassword;
use Tests\Unit\AbstractTestCase;

class SetPasswordTest extends AbstractTestCase
{
    protected SetPassword $setPassword;

    protected function setUp(): void
    {
        parent::setUp();

        $this->setPassword = new SetPassword($this->respondentService);
    }

    public function testResponse(): void
    {
        $this->request->method('getHeader')->willReturn(['Test.124!']);

        $this->request->method('getAttribute')
            ->willReturn('xx.xx.xxx');

        $response = $this->setPassword->execute($this->request);

        self::assertInstanceOf(Response::class, $response);
        self::assertEquals(202, $response->getStatusCode());
    }

    public function testResponse_passwordMissing(): void
    {
        $this->expectExceptionCode(400);
        $this->expectException(Exception::class);

        $this->setPassword->execute($this->request);
    }

    public function testResponse_passwordRuleError(): void
    {
        $this->expectExceptionCode(400);
        $this->expectException(Exception::class);

        $this->request->method('getHeader')->willReturn(['test.124!']);

        $this->setPassword->execute($this->request);
    }
}
