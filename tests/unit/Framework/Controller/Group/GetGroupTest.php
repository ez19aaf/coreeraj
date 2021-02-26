<?php


namespace Tests\Unit\Framework\Controller\Group;


use Slim\Http\Response;
use Survey54\Reap\Framework\Controller\Group\GetGroups;
use Survey54\Reap\Framework\Validator\Group\GetGroupsValidator;
use Tests\Unit\AbstractTestCase;

class GetGroupTest extends AbstractTestCase
{
    protected GetGroups $getGroups;
    protected GetGroupsValidator $getGroupsValidator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->getGroupsValidator = $this->createMock(GetGroupsValidator::class);
        $this->getGroupsValidator->method('validate');
        $this->getGroups = new GetGroups($this->groupService, $this->getGroupsValidator);
    }

    public function testResponse(): void
    {
        $response = $this->getGroups->execute($this->request);

        self::assertInstanceOf(Response::class, $response);
        self::assertEquals(200, $response->getStatusCode());
    }
}