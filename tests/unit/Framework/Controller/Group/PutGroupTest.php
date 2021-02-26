<?php


namespace Tests\Unit\Framework\Controller\Group;

use Slim\Http\Response;
use Survey54\Reap\Framework\Controller\Group\PutGroup;
use Survey54\Reap\Framework\Validator\Group\PostGroupValidator;
use Tests\Unit\AbstractTestCase;

class PutGroupTest extends AbstractTestCase
{
    protected PutGroup $putGroup;
    protected PostGroupValidator $postGroupValidator;

    protected function setUp(): void
    {
        parent::setUp();

        $this->postGroupValidator = $this->createMock(PostGroupValidator::class);
        $this->postGroupValidator->method('validate')
            ->willReturn([
                'userId' => 'yy.yy.yyy',
                'groupName'  => 'test group',
            ]);
        $this->putGroup = new PutGroup($this->groupService, $this->postGroupValidator);
    }

    public function testResponse(): void
    {
        $this->request->method('getAttribute')
            ->willReturn('xx.xx.xxx');

        $response = $this->putGroup->execute($this->request);

        self::assertInstanceOf(Response::class, $response);
        self::assertEquals(200, $response->getStatusCode());
    }
}