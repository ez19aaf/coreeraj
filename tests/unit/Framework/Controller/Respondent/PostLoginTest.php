<?php

namespace Tests\Unit\Framework\Controller\Respondent;

use Exception;
use Slim\Http\Response;
use Survey54\Reap\Framework\Controller\Respondent\PostLogin;
use Survey54\Reap\Framework\Validator\Respondent\TypeValueValidator;
use Tests\Unit\AbstractTestCase;

class PostLoginTest extends AbstractTestCase
{
    protected PostLogin $postLogin;
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

        $this->postLogin = new PostLogin($this->respondentService, $this->typeValueValidator);
    }

    public function testResponse(): void
    {
        $this->request->method('getHeader')->willReturn(['1234']);

        $response = $this->postLogin->execute($this->request);

        self::assertInstanceOf(Response::class, $response);
        self::assertEquals(202, $response->getStatusCode());
    }

    public function testResponse_respondentNotFound(): void
    {
        $this->expectExceptionCode(400);
        $this->expectException(Exception::class);

        $this->postLogin->execute($this->request);
    }
}
