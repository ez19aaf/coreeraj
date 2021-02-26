<?php

namespace Tests\Unit\Framework\Validator\Response;

use PHPUnit\Framework\TestCase;
use Slim\Http\Request;
use Survey54\Reap\Framework\Validator\Response\PostResponseValidator;

class PostResponseValidatorTest extends TestCase
{
    /** @var PostResponseValidator */
    private $postResponseValidator;
    /** @var Request */
    private $request;

    protected function setUp(): void
    {
        parent::setUp();

        $this->postResponseValidator = new PostResponseValidator();
        $this->request               = $this->createMock(Request::class);
    }

    public function testValidate(): void
    {
        $data = [
            'respondentId' => '38c5ca80-36bf-4c09-a349-c239b5f28202',
            'surveyId'     => '4223b102-877e-4907-ad16-710fc4b58d06',
            'questionId'   => 1,
            'answer'       => null,
            'answerIds'    => [1, 3],
            'answerScale'  => null,
        ];

        $this->request->method('getParsedBody')
            ->willReturn($data);

        $actual = $this->postResponseValidator->validate($this->request);

        self::assertEquals($data, $actual);
    }
}
