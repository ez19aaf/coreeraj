<?php

namespace Tests\Unit\Framework\Controller\Open;

use Slim\Http\Response;
use Survey54\Reap\Framework\Controller\Open\GetGenders;
use Tests\Unit\AbstractTestCase;

class GetGendersTest extends AbstractTestCase
{
    protected GetGenders $getGenders;

    protected function setUp(): void
    {
        parent::setUp();

        $this->getGenders = new GetGenders($this->openService);
    }

    public function testResponse_getGenderslist(): void
    {
        $response = $this->getGenders->execute($this->request);

        self::assertInstanceOf(Response::class, $response);
        self::assertEquals(200, $response->getStatusCode());
    }
}
