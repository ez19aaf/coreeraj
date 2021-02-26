<?php

namespace Tests\Unit\Framework\Controller\Survey;

use Slim\Http\Response;
use Survey54\Reap\Framework\Controller\Survey\GetAirtimeLogsForSurvey;
use Tests\Unit\AbstractTestCase;

class GetAirtimeLogsTest extends AbstractTestCase
{
    protected GetAirtimeLogsForSurvey $getAirtimeLogs;

    protected function setUp(): void
    {
        parent::setUp();

        $this->getAirtimeLogs = new GetAirtimeLogsForSurvey($this->surveyService);
    }

    public function testResponse(): void
    {
        $this->request->method('getAttribute')
            ->willReturn('6ac5d72a-40e8-11e9-b210-d663bd873d93');

        $response = $this->getAirtimeLogs->execute($this->request);

        self::assertInstanceOf(Response::class, $response);
        self::assertEquals(200, $response->getStatusCode());
    }
}