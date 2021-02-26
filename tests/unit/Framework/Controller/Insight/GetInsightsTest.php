<?php

namespace Tests\Unit\Framework\Controller\Insight;

use Slim\Http\Response;
use Slim\Http\Uri;
use Survey54\Library\Controller\RestPagination;
use Survey54\Reap\Framework\Controller\Insight\GetInsights;
use Survey54\Reap\Framework\Validator\Insight\GetInsightsValidator;
use Tests\Unit\AbstractTestCase;

class GetInsightsTest extends AbstractTestCase
{
    /** @var GetInsights */
    protected $getInsights;
    /** @var GetInsightsValidator */
    protected $getInsightsValidator;

    protected function setUp(): void
    {
        parent::setUp();

        $this->getInsightsValidator = $this->createMock(GetInsightsValidator::class);
        $this->getInsightsValidator->method('validate')
            ->willReturn([
                'surveyId' => 'xxx.xxx.xxx',
                'page'     => 0,
                'limit'    => 1,
            ]);

        $this->getInsights = new GetInsights($this->insightService, $this->getInsightsValidator);
    }

    public function testResponse(): void
    {
        $list = [self::$insightData];

        $this->request->method('getQueryParams')
            ->willReturn([
                'page'  => 0,
                'limit' => 1,
            ]);

        $this->request->method('getUri')
            ->willReturn(Uri::createFromString('http://localhost:8080/insights'));

        $this->insightService->method('count')
            ->willReturn(1);
        $this->insightService->method('list')
            ->willReturn($list);

        /** @var Response $response */
        $response = $this->getInsights->execute($this->request);

        $paginate = new RestPagination($this->request, 1, 0, 1);
        $expected = $paginate->buildPaginatedStructure($list);

        self::assertInstanceOf(Response::class, $response);
        self::assertEquals(200, $response->getStatusCode());
        self::assertEquals($expected, json_decode($response->getBody(), true, 512, JSON_THROW_ON_ERROR));
    }
}
