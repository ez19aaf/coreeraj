<?php

namespace Tests\ApiTest\Survey;

use Tests\ApiTest\AbstractTestCase;

class GetSurveyListTest extends AbstractTestCase
{
    public function testGetSurveyList(): void
    {
        $permissions = json_encode(['reap' => ['surveys' => ['+read' => []]]], JSON_THROW_ON_ERROR);

        $response = $this->app->get('/surveys?respondentId=' . $this->responseSurveyId . '&country=["South Africa"]', $this->tokenOption($permissions));

        self::assertEquals(200, $response->getStatusCode());
    }
}
