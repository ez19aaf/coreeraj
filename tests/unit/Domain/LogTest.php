<?php

namespace Tests\Unit\Domain;

use Survey54\Reap\Domain\Log;
use Tests\Unit\AbstractTestCase;

class LogTest extends AbstractTestCase
{
    public function testInsight(): void
    {
        $data = self::$logData;

        $log = new Log(
            $data['uuid'],
            $data['objectId'],
            $data['objectType'],
            $data['action'],
            $data['request'],
            $data['response'],
        );

        self::assertEquals($data['uuid'], $log->uuid);
        self::assertEquals($data['objectId'], $log->objectId);
        self::assertEquals($data['objectType'], $log->objectType);
        self::assertEquals($data['action'], $log->action);
        self::assertEquals($data['request'], $log->request);
        self::assertEquals($data['response'], $log->response);

        self::assertNull($log->createdAt);

        $actualData = $log->toArray();

        self::assertEquals($data, $actualData);
    }

    public function testBuild(): void
    {
        $data = self::$logData;

        $actual = Log::build($data, true);

        self::assertEquals($data, $actual->toArray());
    }
}
