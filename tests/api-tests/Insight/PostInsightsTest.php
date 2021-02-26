<?php

namespace Tests\ApiTest\Insight;

use Tests\ApiTest\AbstractTestCase;

/** @see /insights */
class PostInsightsTest extends AbstractTestCase
{
    public function testPostInsights_ValidResponse(): void
    {
        $permissions = json_encode(['reap' => ['insights' => ['+create' => []]]], JSON_THROW_ON_ERROR);

        $options         = $this->tokenOption($permissions);
        $options['json'] = [
            'surveyId' => $this->insightSurveyId,
            'userId'   => $this->userId,
            'summary'  => [
                'This is the first summary.',
                'This is the second summary.',
                'This is the third summary.',
                'This is the fourth summary.',
                'This is the fifth summary.',
            ],
        ];

        $response = $this->app->post('/insights', $options);

        self::assertEquals(202, $response->getStatusCode());
    }
}
