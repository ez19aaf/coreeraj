<?php

namespace Tests\Unit\Framework\Controller\Respondent;

use Slim\Http\Response;
use Survey54\Reap\Framework\Controller\Respondent\GetRespondentSurveyHistory;
use Tests\Unit\AbstractTestCase;

class GetRespondentSurveyHistoryTest extends AbstractTestCase
{
    protected GetRespondentSurveyHistory $getRespondentSurveyHistory;

    protected function setUp(): void
    {
        parent::setUp();

        $this->getRespondentSurveyHistory = new GetRespondentSurveyHistory($this->respondentService);
    }

    public function testResponse(): void
    {
        $this->request->method('getAttribute')
            ->willReturn('xx.xx.xxx');

        $response = $this->getRespondentSurveyHistory->execute($this->request);

        self::assertInstanceOf(Response::class, $response);
        self::assertEquals(200, $response->getStatusCode());
    }
}
