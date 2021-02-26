<?php

namespace Tests\Unit\Framework\Controller\Respondent;

use Slim\Http\Response;
use Survey54\Reap\Framework\Controller\Respondent\PutProfileImage;
use Survey54\Reap\Framework\Validator\Respondent\PutProfileImageValidator;
use Tests\Unit\AbstractTestCase;

class PutProfileImageTest extends AbstractTestCase
{
    protected PutProfileImage $putProfileImage;
    protected PutProfileImageValidator $putProfileImageValidator;

    protected function setUp(): void
    {
        parent::setUp();

        $this->putProfileImageValidator = $this->createMock(PutProfileImageValidator::class);
        $this->putProfileImageValidator->method('validate')
            ->willReturn([
                'image' => 'encodedProfileImage',
            ]);
        $this->putProfileImage = new PutProfileImage($this->respondentService, $this->putProfileImageValidator);
    }

    public function testResponse(): void
    {
        $this->request->method('getAttribute')
            ->willReturn('xx.xx.xxx');

        $response = $this->putProfileImage->execute($this->request);

        self::assertInstanceOf(Response::class, $response);
        self::assertEquals(202, $response->getStatusCode());
    }
}
