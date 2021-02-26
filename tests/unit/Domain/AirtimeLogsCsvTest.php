<?php


namespace Tests\Unit\Domain;


use Survey54\Reap\Domain\AirtimeLogsCsv;
use Tests\Unit\AbstractTestCase;

class AirtimeLogsCsvTest extends AbstractTestCase
{
    public function testAirtimeLogsCsv()
    {
        $data = self::$airtimeLog;
        $log  = new AirtimeLogsCsv(
            $data['uuid'],
            $data['mobile'],
            $data['redeemed'],
            $data['proof'],
            $data['errored'],
            $data['error'],
            $data['createdAt'],
            $data['updatedAt']
        );

        self::assertEquals($data['uuid'], $log->uuid);
        self::assertEquals($data['mobile'], $log->mobile);
        self::assertEquals($data['redeemed'], $log->redeemed);
        self::assertEquals($data['proof'], $log->proof);
        self::assertEquals($data['errored'], $log->errored);
        self::assertEquals($data['error'], $log->error);
        self::assertEquals($data['createdAt'], $log->createdAt);
        self::assertEquals($data['updatedAt'], $log->updatedAt);

        self::assertNull($log->createdAt);

        $actual = $log->toArray();

        self::assertEquals($actual, $data);
    }

    public function testBuild()
    {
        $data   = self::$airtimeLog;
        $actual = AirtimeLogsCsv::build($data, true);
        self::assertEquals($data, $actual->toArray());
    }
}