<?php

namespace Tests\ApiTest\Survey;

use Tests\ApiTest\AbstractTestCase;

class GetSurveyRespondentEmailsTest extends AbstractTestCase
{
    public function testGetSurveyRespondentEmails(): void
    {
        $permissions = json_encode(['admin' => ['endpoint' => ['+read' => []]]], JSON_THROW_ON_ERROR);

        $response = $this->app->get('/surveys/' . $this->responseSurveyId . '/emails', $this->tokenOption($permissions));
        $data     = $this->getData($response);

        self::assertEquals(200, $response->getStatusCode());
        self::assertCount(1, $data);
    }
}
