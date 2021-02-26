<?php

namespace Tests\Unit\Domain;

use Survey54\Reap\Domain\Insight;
use Tests\Unit\AbstractTestCase;

class InsightTest extends AbstractTestCase
{
    public function testInsight(): void
    {
        $data = self::$insightData;

        $insight = new Insight(
            $data['uuid'],
            $data['userId'],
            $data['surveyId'],
            $data['summary']
        );

        self::assertEquals($data['uuid'], $insight->uuid);
        self::assertEquals($data['userId'], $insight->userId);
        self::assertEquals($data['surveyId'], $insight->surveyId);
        self::assertEquals($data['summary'], $insight->summary);

        self::assertNull($insight->createdAt);

        $actualData = $insight->toArray();

        self::assertEquals($data, $actualData);
    }

    public function testBuild(): void
    {
        $data = self::$insightData;

        $actual = Insight::build($data, true);

        self::assertEquals($data, $actual->toArray());
    }
}
