<?php

namespace Tests\Unit\Framework\Validator\Insight;

use PHPUnit\Framework\TestCase;
use Slim\Http\Request;
use Survey54\Reap\Framework\Validator\Insight\PostInsightsValidator;

class PostInsightsValidatorTest extends TestCase
{
    private PostInsightsValidator $postInsightsValidator;
    /** @var Request */
    private $request;

    protected function setUp(): void
    {
        parent::setUp();

        $this->postInsightsValidator = new PostInsightsValidator();
        $this->request               = $this->createMock(Request::class);
    }

    public function testValidate(): void
    {
        $data = [
            'userId'   => '1dbb906f-3299-47c2-adee-13492a35fcbb',
            'surveyId' => '38c5ca80-36bf-4c09-a349-c239b5f28202',
            'summary'  => [
                'This is the first insight',
                'This is the second insight',
                'This is the third insight',
            ],
        ];

        $this->request->method('getParsedBody')
            ->willReturn($data);

        $actual = $this->postInsightsValidator->validate($this->request);

        self::assertEquals($data, $actual);
    }
}
