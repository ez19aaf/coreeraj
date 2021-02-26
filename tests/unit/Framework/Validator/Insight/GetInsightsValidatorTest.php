<?php

namespace Tests\Unit\Framework\Validator\Insight;

use PHPUnit\Framework\TestCase;
use Slim\Http\Request;
use Survey54\Reap\Framework\Validator\Insight\GetInsightsValidator;

class GetInsightsValidatorTest extends TestCase
{
    /** @var GetInsightsValidator */
    private $getInsightsValidator;
    /** @var Request */
    private $request;

    protected function setUp(): void
    {
        parent::setUp();

        $this->getInsightsValidator = new GetInsightsValidator();
        $this->request              = $this->createMock(Request::class);
    }

    public function testValidate(): void
    {
        $data = [
            'surveyId' => '1dbb906f-3299-47c2-adee-13492a35fcbb',
            'page'     => 1,
            'limit'    => 1,
        ];

        $this->request->method('getQueryParams')
            ->willReturn($data);

        $actual = $this->getInsightsValidator->validate($this->request);

        self::assertEquals($data, $actual);
    }
}
