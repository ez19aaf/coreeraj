<?php

namespace Tests\ApiTest\Open;

use Survey54\Library\Domain\Values\Race;
use Tests\ApiTest\AbstractTestCase;

class GetOpenRaceTest extends AbstractTestCase
{
    public function testGetOpenRace(): void
    {
        $permissions = json_encode(['reap' => ['open' => ['+read' => []]]], JSON_THROW_ON_ERROR);

        $response = $this->app->get('/open/races', $this->tokenOption($permissions));
        $data     = $this->getData($response);

        self::assertEquals(200, $response->getStatusCode());
        $expected = Race::toArray();
        self::assertEquals($expected, $data);
    }
}
