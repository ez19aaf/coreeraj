<?php

namespace Tests\Unit\Framework\Controller\Survey;

use Exception;
use Slim\Http\Response;
use Survey54\Reap\Framework\Controller\Survey\PostAirtimeForSurvey;
use Tests\Unit\AbstractTestCase;

class PostAirtimeTest extends AbstractTestCase
{
    protected PostAirtimeForSurvey $postAirtime;

    protected function setUp(): void
    {
        parent::setUp();

        $this->postAirtime = new PostAirtimeForSurvey($this->surveyService);
        $this->postAirtime->setProjectRoot('.');
    }

    public function testResponse(): void
    {
        $this->request->method('getAttribute')
            ->willReturn('6ac5d72a-40e8-11e9-b210-d663bd873d93');

        $this->request->method('getParsedBody')
            ->willReturn([
                'count' => 0,
                'limit' => 0,
            ]);

        $response = $this->postAirtime->execute($this->request);

        self::assertInstanceOf(Response::class, $response);
        self::assertEquals(202, $response->getStatusCode());
    }

    public function testResponse_bodyMissing(): void
    {
        $this->expectExceptionCode(400);
        $this->expectException(Exception::class);

        $this->request->method('getAttribute')
            ->willReturn('6ac5d72a-40e8-11e9-b210-d663bd873d93');

        $this->postAirtime->execute($this->request);
    }
}
