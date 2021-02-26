<?php

namespace Tests\Unit\Framework\Controller\Respondent;

use Slim\Http\Response;
use Survey54\Reap\Framework\Controller\Respondent\ForgotPassword;
use Survey54\Reap\Framework\Validator\Respondent\TypeValueValidator;
use Tests\Unit\AbstractTestCase;

class ForgotPasswordTest extends AbstractTestCase
{
    protected ForgotPassword $forgotPassword;
    protected TypeValueValidator $typeValueValidator;

    protected function setUp(): void
    {
        parent::setUp();

        $this->typeValueValidator = $this->createMock(TypeValueValidator::class);
        $this->typeValueValidator->method('validate')
            ->willReturn([
                'type'  => 'MOBILE',
                'value' => '1234567890',
            ]);

        $this->forgotPassword = new ForgotPassword($this->respondentService, $this->typeValueValidator);
    }

    public function testResponse(): void
    {
        $response = $this->forgotPassword->execute($this->request);

        self::assertInstanceOf(Response::class, $response);
        self::assertEquals(202, $response->getStatusCode());
    }
}
