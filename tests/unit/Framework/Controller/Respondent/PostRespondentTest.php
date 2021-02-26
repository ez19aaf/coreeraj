<?php

namespace Tests\Unit\Framework\Controller\Respondent;

use Exception;
use Slim\Http\Response;
use Survey54\Reap\Framework\Controller\Respondent\PostRespondent;
use Survey54\Reap\Framework\Validator\Respondent\PostRespondentValidator;
use Tests\Unit\AbstractTestCase;

class PostRespondentTest extends AbstractTestCase
{
    protected PostRespondent $postRespondent;
    protected PostRespondentValidator $postRespondentValidator;

    protected function setUp(): void
    {
        parent::setUp();

        $this->postRespondentValidator = $this->createMock(PostRespondentValidator::class);
        $this->postRespondentValidator->method('validate')
            ->willReturn([
                'mobile' => '1234567890',
                'email'  => 'test@test.com',
            ]);
        $this->postRespondent = new PostRespondent($this->respondentService, $this->postRespondentValidator);
    }

    public function testResponse(): void
    {
        $this->request->method('getHeader')->willReturn(['Test.124!']);

        $response = $this->postRespondent->execute($this->request);

        self::assertInstanceOf(Response::class, $response);
        self::assertEquals(202, $response->getStatusCode());
    }

    public function testResponse_passwordMissing(): void
    {
        $this->expectExceptionCode(400);
        $this->expectException(Exception::class);

        $this->postRespondent->execute($this->request);
    }

    public function testResponse_passwordRuleError(): void
    {
        $this->expectExceptionCode(400);
        $this->expectException(Exception::class);

        $this->request->method('getHeader')->willReturn(['test.124!']);

        $this->postRespondent->execute($this->request);
    }
}
