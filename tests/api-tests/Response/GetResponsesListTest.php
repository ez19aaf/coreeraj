<?php

namespace Tests\ApiTest\Response;

use Tests\ApiTest\AbstractTestCase;

class GetResponsesListTest extends AbstractTestCase
{
    public function testGetResponsesList_surveyIdValidation(): void
    {
        $permissions = json_encode(['reap' => ['responses' => ['+read' => []]]], JSON_THROW_ON_ERROR);

        $response = $this->app->get('/responses', $this->tokenOption($permissions));

        self::assertEquals(400, $response->getStatusCode());
    }

    public function testGetResponsesList(): void
    {
        $permissions = json_encode(['reap' => ['responses' => ['+read' => ['*userId' => [$this->userId]]]]], JSON_THROW_ON_ERROR);

        $response = $this->app->get('/responses?surveyId=' . $this->responseSurveyId, $this->tokenOption($permissions));
        $data     = $this->getData($response);

        self::assertEquals(200, $response->getStatusCode());
        self::assertArrayHasKey('data', $data);
    }
}
