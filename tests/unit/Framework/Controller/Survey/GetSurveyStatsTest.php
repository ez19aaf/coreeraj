<?php


namespace Tests\Unit\Framework\Controller\Survey;


use Slim\Http\Response;
use Survey54\Reap\Framework\Controller\Survey\GetSurveyStats;
use Tests\Unit\AbstractTestCase;

class GetSurveyStatsTest extends AbstractTestCase
{
    protected GetSurveyStats $getSurveyStats;

    protected function setUp(): void
    {
        parent::setUp();
        $this->getSurveyStats = new GetSurveyStats($this->surveyService);
    }

    public function testWillReturnData(): void
    {
        $this->surveyService->method('getSurveyStats')
            ->willReturn([
                'numberOfOpenSurveys'             => 10,
                'averageCompletionRatePercentage' => 20,
                'numberOfSurveyRespondents'       => 200,
            ]);
        $this->request->method('getAttribute')
            ->with('uuid')
            ->willReturn($this->respondent->uuid);
        $response = $this->getSurveyStats->execute($this->request);

        self::assertInstanceOf(Response::class, $response);
        self::assertEquals(200, $response->getStatusCode());
    }
}
