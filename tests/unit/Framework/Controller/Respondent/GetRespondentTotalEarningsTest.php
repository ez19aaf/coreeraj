<?php

namespace Tests\Unit\Framework\Controller\Respondent;

use Slim\Http\Response;
use Survey54\Reap\Framework\Controller\Respondent\GetRespondentTotalEarnings;
use Tests\Unit\AbstractTestCase;

class GetRespondentTotalEarningsTest extends AbstractTestCase
{
    protected GetRespondentTotalEarnings $getRespondentTotalEarnings;

    protected function setUp(): void
    {
        parent::setUp();

        $this->getRespondentTotalEarnings = new GetRespondentTotalEarnings($this->respondentService);
    }

    public function testResponse(): void
    {
        $this->request->method('getAttribute')
            ->willReturn('xx.xx.xxx');

        $response = $this->getRespondentTotalEarnings->execute($this->request);

        self::assertInstanceOf(Response::class, $response);
        self::assertEquals(200, $response->getStatusCode());
    }
}
