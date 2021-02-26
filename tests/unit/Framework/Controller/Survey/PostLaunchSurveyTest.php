<?php

namespace Tests\Unit\Framework\Controller\Survey;

use Slim\Http\Response;
use Survey54\Reap\Framework\Controller\Survey\PostLaunchSurvey;
use Tests\Unit\AbstractTestCase;

class PostLaunchSurveyTest extends AbstractTestCase
{
    protected PostLaunchSurvey $postLaunchSurvey;

    protected function setUp(): void
    {
        parent::setUp();

        $this->postLaunchSurvey = new PostLaunchSurvey($this->surveyService);
    }

    public function testResponse(): void
    {
        $this->request->method('getAttribute')
            ->willReturn('6ac5d72a-40e8-11e9-b210-d663bd873d93');

        /** @var Response $response */
        $response = $this->postLaunchSurvey->execute($this->request);

        self::assertInstanceOf(Response::class, $response);
        self::assertEquals(202, $response->getStatusCode());
    }
}
