<?php

namespace Tests\ApiTest\Survey;

use Tests\ApiTest\AbstractTestCase;

class PostSurveyAirtimeTest extends AbstractTestCase
{
    public function testPostAirtime(): void
    {
        $permissions = json_encode(['reap' => ['surveys' => ['+create' => []]]], JSON_THROW_ON_ERROR);

        $options         = $this->tokenOption($permissions);
        $options['json'] = [
            "retry" => false,
        ];

        $response = $this->app->post('/surveys/' . $this->responseSurveyId . '/airtime', $options);

        self::assertEquals(202, $response->getStatusCode());
    }

    public function testDeleteSurvey(): void
    {
        $permissions = json_encode(['admin' => ['endpoint' => ['+delete' => []]]], JSON_THROW_ON_ERROR);

        $response = $this->app->delete('/surveys/' . $this->responseSurveyId, $this->tokenOption($permissions));

        self::assertEquals(202, $response->getStatusCode());
    }
}
