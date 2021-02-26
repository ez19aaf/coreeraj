<?php

namespace Tests\ApiTest\Survey;

use Tests\ApiTest\AbstractTestCase;

class GetSurveyStatusTest extends AbstractTestCase
{
    public function testGetSurveyStatus(): void
    {
        $permissions = json_encode(['reap' => ['surveys' => ['+read' => []]]], JSON_THROW_ON_ERROR);

        $response = $this->app->get('/surveys/' . $this->responseSurveyId . '/status', $this->tokenOption($permissions));
        $data     = $this->getData($response);

        self::assertEquals(200, $response->getStatusCode());
        self::assertArrayHasKey('expected', $data);
        self::assertArrayHasKey('completed', $data);
        self::assertArrayHasKey('incomplete', $data);
    }
}
