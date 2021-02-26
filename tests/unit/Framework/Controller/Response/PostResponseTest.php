<?php

namespace Tests\Unit\Framework\Controller\Response;

use Slim\Http\Response;
use Survey54\Reap\Framework\Controller\Response\PostResponse;
use Survey54\Reap\Framework\Validator\Response\PostResponseValidator;
use Tests\Unit\AbstractTestCase;

class PostResponseTest extends AbstractTestCase
{
    /** @var PostResponse */
    protected $postResponse;
    /** @var PostResponseValidator */
    protected $postResponseValidator;

    protected function setUp(): void
    {
        parent::setUp();

        $this->postResponseValidator = $this->createMock(PostResponseValidator::class);
        $this->postResponseValidator->method('validate')
            ->willReturn(['response-data']);

        $this->postResponse = new PostResponse($this->responseService, $this->postResponseValidator);
    }

    public function testResponse(): void
    {
        /** @var Response $response */
        $response = $this->postResponse->execute($this->request);

        self::assertInstanceOf(Response::class, $response);
        self::assertEquals(202, $response->getStatusCode());
    }
}
