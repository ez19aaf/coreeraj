<?php

namespace Tests\Unit\Framework\Controller\Respondent;

use Slim\Http\Response;
use Survey54\Reap\Framework\Controller\Respondent\RemoveProfileImage;
use Tests\Unit\AbstractTestCase;

class RemoveProfileImageTest extends AbstractTestCase
{
    protected RemoveProfileImage $removeProfileImage;

    protected function setUp(): void
    {
        parent::setUp();

        $this->removeProfileImage = new RemoveProfileImage($this->respondentService);
    }

    public function testResponse(): void
    {
        $this->request->method('getAttribute')
            ->willReturn('xx.xx.xxx');

        $response = $this->removeProfileImage->execute($this->request);

        self::assertInstanceOf(Response::class, $response);
        self::assertEquals(202, $response->getStatusCode());
    }
}
