<?php

namespace Tests\Unit\Framework\Controller\Respondent;

use Slim\Http\Response;
use Survey54\Reap\Framework\Controller\Respondent\SendNewVerificationCode;
use Survey54\Reap\Framework\Validator\Respondent\SendNewVerificationCodeValidator;
use Tests\Unit\AbstractTestCase;

class SendNewVerificationCodeTest extends AbstractTestCase
{
    protected SendNewVerificationCode $sendNewVerificationCode;
    protected SendNewVerificationCodeValidator $sendNewVerificationCodeValidator;

    protected function setUp(): void
    {
        parent::setUp();

        $this->sendNewVerificationCodeValidator = $this->createMock(SendNewVerificationCodeValidator::class);
        $this->sendNewVerificationCodeValidator->method('validate')
            ->willReturn([
                'type'                => 'MOBILE',
                'value'               => '12345',
                'oldVerificationCode' => '12345',
            ]);
        $this->sendNewVerificationCode = new SendNewVerificationCode($this->respondentService, $this->sendNewVerificationCodeValidator);
    }

    public function testResponse(): void
    {
        $response = $this->sendNewVerificationCode->execute($this->request);

        self::assertInstanceOf(Response::class, $response);
        self::assertEquals(202, $response->getStatusCode());
    }
}
