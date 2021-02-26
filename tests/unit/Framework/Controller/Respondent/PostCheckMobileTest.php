<?php

namespace Tests\Unit\Framework\Controller\Respondent;

use Slim\Http\Response;
use Survey54\Reap\Framework\Controller\Respondent\PostCheckMobile;
use Survey54\Reap\Framework\Validator\Respondent\PostCheckMobileValidator;
use Tests\Unit\AbstractTestCase;

class PostCheckMobileTest extends AbstractTestCase
{

    protected PostCheckMobile $postCheckMobile;
    protected PostCheckMobileValidator $postCheckMobileValidator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->postCheckMobileValidator = $this->createMock(PostCheckMobileValidator::class);
        $this->postCheckMobile          = new PostCheckMobile($this->respondentService, $this->postCheckMobileValidator);
    }

    public function testWillRespond(): void
    {
        $data = [
            'mobile' => '+447879981815',
        ];
        $this->postCheckMobileValidator->method('validate')
            ->willReturn($data);
        $this->respondentService->method('checkMobile')
            ->willReturn([
                "respondentId" => null,
                "mobile"       => "+447879981815",
                "exists"       => false,
            ]);
        $this->request->method('getParsedBody')
            ->willReturn($data);
        $response = $this->postCheckMobile->execute($this->request);

        self::assertInstanceOf(Response::class, $response);
        self::assertEquals(202, $response->getStatusCode());
    }
}
