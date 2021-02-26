<?php

namespace Tests\ApiTest\Survey;

use Tests\ApiTest\AbstractTestCase;

class GetAirtimeLogsTest extends AbstractTestCase
{
    public function testGetAirtimeLogs(): void
    {
        $permissions = json_encode(['reap' => ['surveys' => ['+read' => []]]], JSON_THROW_ON_ERROR);

        $response = $this->app->get('/surveys/' . $this->responseSurveyId . '/airtime-logs', $this->tokenOption($permissions));
        $data     = $this->getData($response);

        $expected = [];

        self::assertEquals(200, $response->getStatusCode());
        self::assertEquals($expected, $data);
    }
}