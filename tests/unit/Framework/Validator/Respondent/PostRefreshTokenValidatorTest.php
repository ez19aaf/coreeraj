<?php

namespace Tests\Unit\Framework\Validator\Respondent;

use Survey54\Reap\Framework\Validator\Respondent\PostRefreshTokenValidator;
use Tests\Unit\AbstractTestCase;

class PostRefreshTokenValidatorTest extends AbstractTestCase
{
    protected PostRefreshTokenValidator $postRefreshTokenValidator;

    protected function setUp(): void
    {
        parent::setUp();

        $this->postRefreshTokenValidator = new PostRefreshTokenValidator();
    }

    public function testValidate(): void
    {
        $data = [
            'refreshToken' => self::$respondentData['refreshToken'],
            'expiredToken' => self::$respondentData['refreshTokenExpiry'],
        ];

        $this->request->method('getParsedBody')
            ->willReturn($data);

        $actual = $this->postRefreshTokenValidator->validate($this->request);

        self::assertEquals($data, $actual);
    }
}
