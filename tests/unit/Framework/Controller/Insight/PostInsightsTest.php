<?php

namespace Tests\Unit\Framework\Controller\Insight;

use Slim\Http\Response;
use Survey54\Reap\Framework\Controller\Insight\PostInsights;
use Survey54\Reap\Framework\Validator\Insight\PostInsightsValidator;
use Tests\Unit\AbstractTestCase;

class PostInsightsTest extends AbstractTestCase
{
    /** @var PostInsights */
    protected $postInsights;
    /** @var PostInsightsValidator */
    protected $postInsightsValidator;

    protected function setUp(): void
    {
        parent::setUp();

        $this->postInsightsValidator = $this->createMock(PostInsightsValidator::class);
        $this->postInsightsValidator->method('validate')
            ->willReturn(['insight-data']);

        $this->postInsights = new postInsights($this->insightService, $this->postInsightsValidator);
    }

    public function testResponse(): void
    {
        /** @var Response $response */
        $response = $this->postInsights->execute($this->request);

        self::assertInstanceOf(Response::class, $response);
        self::assertEquals(202, $response->getStatusCode());
    }
}
