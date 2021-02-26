<?php

namespace Tests\Unit\Framework\Controller\Survey;

use Slim\Http\Response;
use Survey54\Reap\Framework\Controller\Survey\GetSurveyRespondentEmails;
use Tests\Unit\AbstractTestCase;

class GetSurveyRespondentEmailsTest extends AbstractTestCase
{
    protected GetSurveyRespondentEmails $getSurveyRespondentEmails;

    public function fileProvider(): iterable
    {
        $file = fopen('php://temp', 'wrb');
        fputcsv($file, ['Emails'], ';');
        $emails = [['email' => 'respondent@email.com'], ['email' => 'someone@email.com']];
        fputcsv($file, [$emails[0]['email']], ';');
        fputcsv($file, [$emails[0]['email']], ';');
        rewind($file);
        return new EmailsProvider($file);
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->getSurveyRespondentEmails = new GetSurveyRespondentEmails($this->surveyService);
    }

    public function testResponse(): void
    {
        $this->request->method('getAttribute')
            ->willReturn('6ac5d72a-40e8-11e9-b210-d663bd873d93');

        $response = $this->getSurveyRespondentEmails->execute($this->request);

        self::assertInstanceOf(Response::class, $response);
        self::assertEquals(200, $response->getStatusCode());
    }

    /**
     * @dataProvider fileProvider
     */
    public function testWillReturnValidResponse(): void
    {
        $surveyId = $this->survey->uuid;
        $emails   = [['email' => 'respondent@email.com'], ['email' => 'someone@email.com']];
        $this->request->method('getAttribute')
            ->with('uuid')
            ->willReturn($surveyId);
        $this->surveyService->method('getRespondentEmails')
            ->with($surveyId)
            ->willReturn($emails);

        $response = $this->getSurveyRespondentEmails->execute($this->request);
        self::assertInstanceOf(Response::class, $response);
        self::assertEquals(200, $response->getStatusCode());
    }
}
