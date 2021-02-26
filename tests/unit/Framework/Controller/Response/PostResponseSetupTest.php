<?php

namespace Tests\Unit\Framework\Controller\Response;

use Slim\Http\Response;
use Survey54\Reap\Framework\Controller\Response\PostResponseSetup;
use Survey54\Reap\Framework\Validator\Response\PostResponseSetupValidator;
use Tests\Unit\AbstractTestCase;

class PostResponseSetupTest extends AbstractTestCase
{
    /** @var PostResponseSetup */
    protected $postResponseSetup;
    /** @var PostResponseSetupValidator */
    protected $postResponseSetupValidator;

    protected function setUp(): void
    {
        parent::setUp();

        $this->postResponseSetupValidator = $this->createMock(PostResponseSetupValidator::class);
        $this->postResponseSetupValidator->method('validate')
            ->willReturn(['response-data']);

        $this->postResponseSetup = new PostResponseSetup($this->responseService, $this->postResponseSetupValidator);
    }

    public function testResponse(): void
    {
        /** @var Response $response */
        $response = $this->postResponseSetup->execute($this->request);

        self::assertInstanceOf(Response::class, $response);
        self::assertEquals(202, $response->getStatusCode());
    }
}
