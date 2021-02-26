<?php

namespace Tests\Unit\Domain;

use Survey54\Reap\Domain\RespondentSurvey;
use Tests\Unit\AbstractTestCase;

class RespondentSurveyTest extends AbstractTestCase
{
    public function testRespondentSurvey(): void
    {
        $data = self::$respondentSurveyData;

        $respondent                 = new RespondentSurvey(
            $data['uuid'],
            $data['respondentId'],
            $data['surveyId'],
        );
        $respondent->status         = $data['status'];
        $respondent->ipAddress      = $data['ipAddress'];
        $respondent->nextQuestionId = $data['nextQuestionId'];

        self::assertEquals($data['uuid'], $respondent->uuid);
        self::assertEquals($data['respondentId'], $respondent->respondentId);
        self::assertEquals($data['surveyId'], $respondent->surveyId);
        self::assertEquals($data['status'], $respondent->status);
        self::assertEquals($data['ipAddress'], $respondent->ipAddress);

        self::assertNull($respondent->createdAt);

        $actualData = $respondent->toArray();

        self::assertEquals($data, $actualData);
    }

    public function testBuild(): void
    {
        $data = self::$respondentSurveyData;

        $actual = RespondentSurvey::build($data, true);

        self::assertEquals($data, $actual->toArray());
    }
}
