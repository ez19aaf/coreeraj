<?php

namespace Tests\Unit\Framework\Controller\Open;

use Slim\Http\Response;
use Survey54\Reap\Framework\Controller\Open\GetRaces;
use Tests\Unit\AbstractTestCase;

class GetRacesTest extends AbstractTestCase
{
    protected GetRaces $getRaces;

    protected function setUp(): void
    {
        parent::setUp();

        $this->getRaces = new GetRaces($this->openService);
    }

    public function testResponse_getGenderslist(): void
    {
        $response = $this->getRaces->execute($this->request);

        self::assertInstanceOf(Response::class, $response);
        self::assertEquals(200, $response->getStatusCode());
    }
}
