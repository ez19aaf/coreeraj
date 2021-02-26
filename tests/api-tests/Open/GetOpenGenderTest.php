<?php

namespace Tests\ApiTest\Open;

use Survey54\Library\Domain\Values\Gender;
use Tests\ApiTest\AbstractTestCase;

class GetOpenGenderTest extends AbstractTestCase
{
    public function testGetOpenGender(): void
    {
        $permissions = json_encode(['reap' => ['open' => ['+read' => []]]], JSON_THROW_ON_ERROR);

        $response = $this->app->get('/open/genders', $this->tokenOption($permissions));
        $data     = $this->getData($response);

        self::assertEquals(200, $response->getStatusCode());
        $expected = Gender::toArray();
        self::assertEquals($expected, $data);
    }
}
