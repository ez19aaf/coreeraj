<?php

namespace Tests\Unit\Domain;

use Survey54\Reap\Domain\Gdpr;
use Tests\Unit\AbstractTestCase;

class GdprTest extends AbstractTestCase
{
    public function testGdpr(): void
    {
        $data = self::$gdprData;

        $gdpr = new Gdpr(
            $data['uuid'],
            $data['userId'],
            $data['userType'],
            $data['action'],
            $data['duration']
        );

        self::assertEquals($data['uuid'], $gdpr->uuid);
        self::assertEquals($data['userId'], $gdpr->userId);
        self::assertEquals($data['userType'], $gdpr->userType);
        self::assertEquals($data['action'], $gdpr->action);
        self::assertEquals($data['duration'], $gdpr->duration);

        self::assertNull($gdpr->createdAt);

        $actualData = $gdpr->toArray();

        self::assertEquals($data, $actualData);
    }

    public function testBuild(): void
    {
        $data = self::$gdprData;

        $actual = Gdpr::build($data, true);

        self::assertEquals($data, $actual->toArray());
    }
}
