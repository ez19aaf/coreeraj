<?php


namespace Tests\Unit\Framework\Controller\Group;

use Slim\Http\Response;
use Survey54\Reap\Framework\Controller\Group\DeleteGroup;
use Tests\Unit\AbstractTestCase;

class DeleteGroupTest extends AbstractTestCase
{
    protected DeleteGroup $deleteGroup;

    protected function setUp(): void
    {
        parent::setUp();

        $this->deleteGroup = new DeleteGroup($this->groupService);
    }

    public function testResponse(): void
    {
        $this->request->method('getParsedBody')
            ->willReturn(['xx.xx.xxx']);

        $response = $this->deleteGroup->execute($this->request);

        self::assertInstanceOf(Response::class, $response);
        self::assertEquals(202, $response->getStatusCode());
    }
}