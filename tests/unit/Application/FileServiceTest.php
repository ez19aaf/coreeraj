<?php

namespace Tests\Unit\Application;

use Exception;
use Survey54\Reap\Application\FileService;
use Tests\Unit\AbstractTestCase;

class FileServiceTest extends AbstractTestCase
{
    private array $data; // $requestData

    protected function setUp(): void
    {
        parent::setUp();

        $this->data        = self::$responseData;
        $this->fileService = new FileService(
            $this->surveyRepository,
            $this->responseService,
        );
    }

    public function testSpreadSheetSurveyNotFound(): void
    {
        $this->expectExceptionCode(400);
        $this->expectException(Exception::class);

        $this->fileService->createSpreadSheet($this->survey->uuid);
    }

    public function testCreateSpreadSheet(): void
    {
        $this->surveyRepository->method('find')->willReturn($this->survey);

        $this->data['answer']      = '';
        $this->data['answerScale'] = 1;

        $this->responseService->method('list')->willReturn([$this->data]);

        $this->fileService->createSpreadSheet($this->survey->uuid);

        self::assertTrue(true);
    }

    public function testPdfSurveyNotFound(): void
    {
        $this->expectExceptionCode(400);
        $this->expectException(Exception::class);

        $this->fileService->createPdf($this->survey->uuid);
    }

    public function testCreatePdf(): void
    {
        $this->survey->questions[0]['text'] = 'What is your favourite food? What is your favourite food? What is your favourite food? What is your favourite food? What is your favourite food?';

        $this->surveyRepository->method('find')->willReturn($this->survey);

        $this->data['answer']      = '';
        $this->data['answerScale'] = 1;

        $this->responseService->method('list')->willReturn([$this->data, $this->data]);

        $this->fileService->createPdf($this->survey->uuid);

        self::assertTrue(true);
    }

    public function testPptSurveyNotFound(): void
    {
        $this->expectExceptionCode(400);
        $this->expectException(Exception::class);

        $this->fileService->createPpt($this->survey->uuid);
    }

    public function testCreatePpt(): void
    {
        $this->survey->questions[0]['text'] = 'What is your favourite food? What is your favourite food? What is your favourite food? What is your favourite food? What is your favourite food?';

        $this->surveyRepository->method('find')->willReturn($this->survey);

        $this->data['answer']      = '';
        $this->data['answerScale'] = 1;

        $this->responseService->method('list')->willReturn([$this->data, $this->data]);

        $this->fileService->createPpt($this->survey->uuid);

        self::assertTrue(true);
    }
}
