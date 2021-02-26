<?php


namespace Tests\Unit\Framework\Controller\Survey;


use Slim\Http\Response;
use Survey54\Reap\Framework\Controller\Survey\GetAirtimeCsv;
use Tests\Unit\AbstractTestCase;

class GetAirtimeCsvTest extends AbstractTestCase
{
    /**
     * @var GetAirtimeCsv
     */
    protected GetAirtimeCsv $getAirtimeCsvController;

    protected function setUp(): void
    {
        parent::setUp();
        $this->getAirtimeCsvController = new GetAirtimeCsv($this->surveyService);
    }

    public function testCanReturnResponse()
    {
        $response    = $this->getAirtimeCsvController->execute($this->request);
        $contentType = $response->getHeaders()['Content-Type'] ?? [];
        $actual      = $contentType[0] ?? "";
        self::assertInstanceOf(Response::class, $response);
        self::assertEquals(200, $response->getStatusCode());
        self::assertEquals('application/json', $actual);
    }
}
