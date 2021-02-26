<?php

namespace Tests\ApiTest\Insight;

use Tests\ApiTest\AbstractTestCase;

/** @see /insights */
class GetInsightsTest extends AbstractTestCase
{
    public function testGetInsights_ValidResponse(): void
    {
        $permissions = json_encode(['reap' => ['insights' => ['+read' => []]]], JSON_THROW_ON_ERROR);

        $response = $this->app->get('/insights', $this->tokenOption($permissions));
        $data     = $this->getData($response);

        self::assertEquals(200, $response->getStatusCode());
        self::assertTrue(count($data['data']) >= 5);
    }

    public function testGetInsights_ForSurvey(): void
    {
        $permissions = json_encode(['reap' => ['insights' => ['+read' => ['*userId' => [$this->userId]]]]], JSON_THROW_ON_ERROR);

        $response = $this->app->get('/insights?surveyId=' . $this->insightSurveyId, $this->tokenOption($permissions));
        $data     = $this->getData($response);

        self::assertEquals(200, $response->getStatusCode());
        self::assertArrayHasKey('data', $data);
    }
}
