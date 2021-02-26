<?php

namespace Tests\Unit\Framework\Controller\Respondent;

use Slim\Http\Response;
use Survey54\Reap\Framework\Controller\Respondent\PutRespondent;
use Survey54\Reap\Framework\Validator\Respondent\PutRespondentValidator;
use Tests\Unit\AbstractTestCase;

class PutRespondentTest extends AbstractTestCase
{
    protected PutRespondent $putRespondent;
    protected PutRespondentValidator $postRespondentValidator;

    protected function setUp(): void
    {
        parent::setUp();

        $this->putRespondentValidator = $this->createMock(PutRespondentValidator::class);

        $this->putRespondentValidator->method('validate')
            ->willReturn([
                'profileImage' => 'encodedProfileImage',
                'isCint'       => true,
            ]);
        $this->putRespondent = new PutRespondent($this->respondentService, $this->putRespondentValidator);
    }

    public function testResponse(): void
    {
        $this->request->method('getAttribute')
            ->willReturn('xx.xx.xxx');

        $response = $this->putRespondent->execute($this->request);

        self::assertInstanceOf(Response::class, $response);
        self::assertEquals(200, $response->getStatusCode());
    }
}
