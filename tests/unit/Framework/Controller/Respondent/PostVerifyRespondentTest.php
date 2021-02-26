<?php

namespace Tests\Unit\Framework\Controller\Respondent;

use Slim\Http\Response;
use Survey54\Reap\Framework\Controller\Respondent\PostVerifyRespondent;
use Survey54\Reap\Framework\Validator\Respondent\PostVerifyRespondentValidator;
use Tests\Unit\AbstractTestCase;

class PostVerifyRespondentTest extends AbstractTestCase
{
    protected PostVerifyRespondent $postVerifyRespondent;
    protected PostVerifyRespondentValidator $postVerifyRespondentValidator;

    protected function setUp(): void
    {
        parent::setUp();

        $this->postVerifyRespondentValidator = $this->createMock(PostVerifyRespondentValidator::class);
        $this->postVerifyRespondentValidator->method('validate')
            ->willReturn([
                'type'             => 'MOBILE',
                'value'            => '12345',
                'verificationCode' => '12345',
            ]);
        $this->postVerifyRespondent = new PostVerifyRespondent($this->respondentService, $this->postVerifyRespondentValidator);
    }

    public function testResponse(): void
    {
        $response = $this->postVerifyRespondent->execute($this->request);

        self::assertInstanceOf(Response::class, $response);
        self::assertEquals(202, $response->getStatusCode());
    }
}
