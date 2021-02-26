<?php


namespace Tests\Unit\Framework\Controller\Survey;


use Slim\Http\Response;
use Survey54\Reap\Framework\Controller\Survey\PostAirtimeCsv;
use Survey54\Reap\Framework\Validator\Survey\PostAirtimeCsvValidator;
use Tests\Unit\AbstractTestCase;

class PostAirtimeCsvTest extends AbstractTestCase
{
    protected PostAirtimeCsv $postAirtimeCsvController;
    protected PostAirtimeCsvValidator $postAirtimeCsvValidator;


    protected function setUp(): void
    {
        parent::setUp();
        $this->postAirtimeCsvValidator = $this->createMock(PostAirtimeCsvValidator::class);
        $this->postAirtimeCsvValidator->method('validate')
            ->willReturn(self::$airTimeCsvData);
        $this->postAirtimeCsvController = new PostAirtimeCsv($this->surveyService, $this->postAirtimeCsvValidator);
    }

    public function testResponse()
    {
        $this->postAirtimeCsvController->setProjectRoot('.');
        $response = $this->postAirtimeCsvController->execute($this->request);
        self::assertInstanceOf(Response::class, $response);
        self::assertEquals(202, $response->getStatusCode());
    }
}
