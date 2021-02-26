<?php


namespace Tests\Unit\Framework\Controller\Files;


use Slim\Http\Response;
use Survey54\Reap\Framework\Controller\Files\GetPowerpoint;
use Tests\Unit\AbstractTestCase;

class GetPowerpointTest extends AbstractTestCase
{
    protected $getSlides;

    protected function setUp(): void
    {
        parent::setUp();
        $this->getSlides = new GetPowerpoint($this->fileService);
    }

    public function testGetPowerPoint()
    {
        $this->request->method('getAttribute')
            ->willReturn('xx.xx.xxx');

        $this->fileService->method('createPpt')->willReturn(
            [
                "surveyTitle" => "unitTestPptController",
                "file"        => dirname(__FILE__) . "/_Insights_2020_05_22.pptx",
            ]
        );

        $response = $this->getSlides->execute($this->request);

        self::assertInstanceOf(Response::class, $response);
        self::assertEquals(200, $response->getStatusCode());
    }
}
