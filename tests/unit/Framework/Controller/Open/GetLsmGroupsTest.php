<?php


namespace Tests\Unit\Framework\Controller\Open;


use Slim\Http\Response;
use Survey54\Library\Domain\Values\LsmGroup;
use Survey54\Reap\Framework\Controller\Open\GetLsmGroups;
use Tests\Unit\AbstractTestCase;

class GetLsmGroupsTest extends AbstractTestCase
{
    protected GetLsmGroups $getLsmGroups;

    protected function setUp(): void
    {
        parent::setUp();
        $this->getLsmGroups = new GetLsmGroups($this->openService);
    }

    public function testWillGetResponse(): void
    {
        $this->openService->method('getLsmGroups')
            ->willReturn(LsmGroup::toArray());
        $response = $this->getLsmGroups->execute($this->request);

        self::assertInstanceOf(Response::class, $response);
        self::assertEquals(200, $response->getStatusCode());
    }
}
