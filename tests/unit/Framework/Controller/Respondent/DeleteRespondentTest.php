<?php

namespace Tests\Unit\Framework\Controller\Respondent;

use Slim\Http\Response;
use Survey54\Reap\Framework\Controller\Respondent\DeleteRespondent;
use Tests\Unit\AbstractTestCase;

class DeleteRespondentTest extends AbstractTestCase
{
    protected DeleteRespondent $deleteRespondent;

    protected function setUp(): void
    {
        parent::setUp();

        $this->deleteRespondent = new DeleteRespondent($this->respondentService);
    }

    public function testResponse(): void
    {
        $this->request->method('getAttribute')
            ->willReturn('xx.xx.xxx');

        $response = $this->deleteRespondent->execute($this->request);

        self::assertInstanceOf(Response::class, $response);
        self::assertEquals(202, $response->getStatusCode());
    }
}
