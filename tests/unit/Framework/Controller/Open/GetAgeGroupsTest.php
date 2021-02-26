<?php

namespace Tests\Unit\Framework\Controller\Open;

use Slim\Http\Response;
use Survey54\Reap\Framework\Controller\Open\GetAgeGroups;
use Tests\Unit\AbstractTestCase;

class GetAgeGroupsTest extends AbstractTestCase
{
    protected GetAgeGroups $getAgeGroups;

    protected function setUp(): void
    {
        parent::setUp();

        $this->getAgeGroups = new GetAgeGroups($this->openService);
    }

    public function testResponse_getAgeGroupslist(): void
    {
        $response = $this->getAgeGroups->execute($this->request);

        self::assertInstanceOf(Response::class, $response);
        self::assertEquals(200, $response->getStatusCode());
    }
}
