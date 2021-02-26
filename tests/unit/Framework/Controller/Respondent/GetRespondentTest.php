<?php

namespace Tests\Unit\Framework\Controller\Respondent;

use Slim\Http\Response;
use Survey54\Reap\Framework\Controller\Respondent\GetRespondent;
use Tests\Unit\AbstractTestCase;

class GetRespondentTest extends AbstractTestCase
{
    protected GetRespondent $getRespondent;

    protected function setUp(): void
    {
        parent::setUp();

        $this->getRespondent = new GetRespondent($this->respondentService);
    }

    public function testResponse(): void
    {
        $this->request->method('getAttribute')
            ->willReturn('xx.xx.xxx');

        $this->respondentService->method('find')
            ->willReturn($this->respondent);

        $response = $this->getRespondent->execute($this->request);

        self::assertInstanceOf(Response::class, $response);
        self::assertEquals(200, $response->getStatusCode());
        self::assertEquals(self::$respondentData, json_decode($response->getBody(), true, 512, JSON_THROW_ON_ERROR));
    }
}
