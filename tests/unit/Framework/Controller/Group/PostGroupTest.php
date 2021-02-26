<?php


namespace Tests\Unit\Framework\Controller\Group;


use Slim\Http\Response;
use Survey54\Reap\Framework\Controller\Group\PostGroup;
use Survey54\Reap\Framework\Validator\Group\PostGroupValidator;
use Tests\Unit\AbstractTestCase;

class PostGroupTest extends AbstractTestCase
{

    protected PostGroup $postGroup;
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
        $this->postGroup = new PostGroup($this->groupService, $this->postGroupValidator);
    }

    public function testResponse(): void
    {
        /** @var Response $response */
        $response = $this->postGroup->execute($this->request);

        self::assertInstanceOf(Response::class, $response);
        self::assertEquals(202, $response->getStatusCode());
    }

}