<?php

namespace Tests\Unit\Framework\Adapter;

use Exception;
use GuzzleHttp\Client;
use Survey54\Reap\Framework\Adapter\AirtimeAdapter;
use Tests\Unit\AbstractTestCase;

class AirTimeAdapterTest extends AbstractTestCase
{
    public function testTopUp_JsonException(): void
    {
        $this->expectExceptionCode(400);
        $this->expectException(Exception::class);

        /** @var Client $client */
        $client         = $this->client('post', null, 500);
        $airtimeAdapter = new AirtimeAdapter($client, '');

        $airtimeAdapter->topUp(self::$redemptionData['uuid'], self::$respondentData['mobile'], self::$redemptionData['amountToRedeem']);
    }

    public function testTopUp(): void
    {
        /** @var Client $client */
        $response       = ['status' => 'Success'];
        $client         = $this->client('post', $response);
        $airtimeAdapter = new AirtimeAdapter($client, '');

        $actual = $airtimeAdapter->topUp(self::$redemptionData['uuid'], self::$respondentData['mobile'], self::$redemptionData['amountToRedeem']);

        self::assertSame($response, $actual);
    }

    public function testTopUp_Failed(): void
    {
        $this->expectExceptionCode(400);
        $this->expectException(Exception::class);

        /** @var Client $client */
        $response       = ['status' => 'Failed'];
        $client         = $this->client('post', $response);
        $airtimeAdapter = new AirtimeAdapter($client, '');

        $airtimeAdapter->topUp(self::$redemptionData['uuid'], self::$respondentData['mobile'], self::$redemptionData['amountToRedeem']);
    }
}
