<?php

namespace Tests\ApiTest\Open;

use Survey54\Library\Domain\Values\Employment;
use Tests\ApiTest\AbstractTestCase;

class GetOpenEmploymentTest extends AbstractTestCase
{
    public function testGetOpenEmployment(): void
    {
        $permissions = json_encode(['reap' => ['open' => ['+read' => []]]], JSON_THROW_ON_ERROR);

        $response = $this->app->get('/open/employments', $this->tokenOption($permissions));
        $data     = $this->getData($response);

        self::assertEquals(200, $response->getStatusCode());
        $expected = Employment::toArray();
        self::assertEquals($expected, $data);
    }
}
