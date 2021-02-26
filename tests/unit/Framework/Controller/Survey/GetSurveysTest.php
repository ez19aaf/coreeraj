<?php

namespace Tests\Unit\Framework\Controller\Survey;

use Slim\Http\Response;
use Survey54\Reap\Framework\Controller\Survey\GetSurveys;
use Survey54\Reap\Framework\Validator\Survey\GetSurveysValidator;
use Tests\Unit\AbstractTestCase;

class GetSurveysTest extends AbstractTestCase
{
    protected GetSurveys $getSurveys;
    protected GetSurveysValidator $getSurveysValidator;

    protected function setUp(): void
    {
        parent::setUp();

        $this->getSurveysValidator = $this->createMock(GetSurveysValidator::class);
        $this->getSurveysValidator->method('validate')
            ->willReturn([
                'country'      => 1,
                'respondentId' => 'xxx',
            ]);

        $this->getSurveys = new GetSurveys($this->surveyService, $this->getSurveysValidator);
    }

    public function testResponse(): void
    {
        $response = $this->getSurveys->execute($this->request);

        self::assertInstanceOf(Response::class, $response);
        self::assertEquals(200, $response->getStatusCode());
    }
}
