<?php


namespace Tests\Unit\Framework\Controller\Files;


use Slim\Http\Response;
use Survey54\Reap\Framework\Controller\Files\GetPdf;
use Tests\Unit\AbstractTestCase;

class GetPdfTest extends AbstractTestCase
{
    protected $getPdf;

    protected function setUp(): void
    {
        parent::setUp();
        $this->getPdf = new GetPdf($this->fileService);
    }

    public function testGetSpreadSheet()
    {
        $this->request->method('getAttribute')
            ->willReturn('xx.xx.xxx');

        $this->fileService->method('createPdf')->willReturn(
            [
                "surveyTitle" => "unitTestPdfController",
                "file"        => dirname(__FILE__) . "/_Insights_2020_05_17.pdf",
            ]
        );

        $response = $this->getPdf->execute($this->request);

        self::assertInstanceOf(Response::class, $response);
        self::assertEquals(200, $response->getStatusCode());
    }

}
