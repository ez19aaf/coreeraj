<?php

namespace Tests\Unit\Framework\Controller\Files;

use Slim\Http\Response;
use Survey54\Reap\Framework\Controller\Files\GetSpreadSheet;
use Tests\Unit\AbstractTestCase;

class GetSpreadSheetTest extends AbstractTestCase
{
    protected $getSpreadSheet;

    protected function setUp(): void
    {
        parent::setUp();
        $this->getSpreadSheet = new GetSpreadSheet($this->fileService);
    }

    public function testGetSpreadSheet()
    {
        $this->request->method('getAttribute')
            ->willReturn('xx.xx.xxx');

        $this->fileService->method('createSpreadSheet')->willReturn(
            [
                "surveyTitle" => "unitTestSpreadSheetController",
                "file"        => dirname(__FILE__) . "/_Insights_2020_04_18.xlsx",
            ]
        );

        $response = $this->getSpreadSheet->execute($this->request);

        self::assertInstanceOf(Response::class, $response);
        self::assertEquals(200, $response->getStatusCode());
    }
}
