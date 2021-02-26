<?php

namespace Tests\Unit\Framework\Controller\Respondent;

use Slim\Http\Response;
use Survey54\Reap\Framework\Controller\Respondent\PostRefreshToken;
use Survey54\Reap\Framework\Validator\Respondent\PostRefreshTokenValidator;
use Tests\Unit\AbstractTestCase;

class PostRefreshTokenTest extends AbstractTestCase
{
    protected PostRefreshToken $postRefreshToken;
    protected PostRefreshTokenValidator $postRefreshTokenValidator;

    protected function setUp(): void
    {
        parent::setUp();

        $this->postRefreshTokenValidator = $this->createMock(PostRefreshTokenValidator::class);
        $this->postRefreshTokenValidator->method('validate')
            ->willReturn([
                'expiredToken' => '123',
                'refreshToken' => '12345',
            ]);

        $this->postRefreshToken = new PostRefreshToken($this->respondentService, $this->postRefreshTokenValidator);
    }

    public function testResponse(): void
    {
        $response = $this->postRefreshToken->execute($this->request);

        self::assertInstanceOf(Response::class, $response);
        self::assertEquals(202, $response->getStatusCode());
    }
}
