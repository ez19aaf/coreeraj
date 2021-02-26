<?php

namespace Tests\Unit\Framework\Controller\Survey;

use Slim\Http\Response;
use Survey54\Reap\Framework\Controller\Survey\GetSurveyStatus;
use Tests\Unit\AbstractTestCase;

class GetSurveyStatusTest extends AbstractTestCase
{
    protected GetSurveyStatus $getSurveyStatus;

    protected function setUp(): void
    {
        parent::setUp();

        $this->getSurveyStatus = new GetSurveyStatus($this->surveyService);
    }

    public function testResponse(): void
    {
        $this->request->method('getAttribute')
            ->willReturn('6ac5d72a-40e8-11e9-b210-d663bd873d93');

        $response = $this->getSurveyStatus->execute($this->request);

        self::assertInstanceOf(Response::class, $response);
        self::assertEquals(200, $response->getStatusCode());
    }
}
