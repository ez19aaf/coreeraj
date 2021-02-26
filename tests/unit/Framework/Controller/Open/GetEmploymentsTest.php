<?php

namespace Tests\Unit\Framework\Controller\Open;

use Slim\Http\Response;
use Survey54\Reap\Framework\Controller\Open\GetEmployments;
use Tests\Unit\AbstractTestCase;

class GetEmploymentsTest extends AbstractTestCase
{
    protected GetEmployments $getEmployments;

    protected function setUp(): void
    {
        parent::setUp();

        $this->getEmployments = new GetEmployments($this->openService);
    }

    public function testResponse_getEmploymentList(): void
    {
        $response = $this->getEmployments->execute($this->request);

        self::assertInstanceOf(Response::class, $response);
        self::assertEquals(200, $response->getStatusCode());
    }
}
