<?php

namespace Tests\Unit\Framework\Controller\Open;

use Slim\Http\Response;
use Survey54\Reap\Framework\Controller\Open\LsmCalculator;
use Tests\Unit\AbstractTestCase;

class LsmCalculatorTest extends AbstractTestCase
{
    protected LsmCalculator $lsmCalculator;

    protected function setUp(): void
    {
        parent::setUp();

        $this->lsmCalculator = new LsmCalculator($this->openService);
    }

    public function testResponse_getLsmRecord(): void
    {
        $response = $this->lsmCalculator->execute($this->request);

        self::assertInstanceOf(Response::class, $response);
        self::assertEquals(200, $response->getStatusCode());
    }
}
