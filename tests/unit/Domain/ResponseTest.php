<?php

namespace Tests\Unit\Domain;

use Survey54\Reap\Domain\Response;
use Tests\Unit\AbstractTestCase;

class ResponseTest extends AbstractTestCase
{
    public function testResponse(): void
    {
        $data = self::$responseData;

        $response = new Response(
            $data['uuid'],
            $data['respondentId'],
            $data['surveyId'],
            $data['questionId'],
        );
        $response->goto        = $data['goto'];
        $response->answer      = $data['answer'];
        $response->answerIds   = $data['answerIds'];
        $response->answerRank  = $data['answerRank'];
        $response->answerScale = $data['answerScale'];
        $response->ageGroup    = $data['ageGroup'];
        $response->gender      = $data['gender'];
        $response->employment  = $data['employment'];
        $response->race        = $data['race'];
        $response->lsmGroup    = $data['lsmGroup'];
        $response->boundTime   = $data['boundTime'];
        $response->boundDate   = $data['boundDate'];

        self::assertEquals($data['uuid'], $response->uuid);
        self::assertEquals($data['respondentId'], $response->respondentId);
        self::assertEquals($data['surveyId'], $response->surveyId);
        self::assertEquals($data['questionId'], $response->questionId);
        self::assertEquals($data['goto'], $response->goto);
        self::assertEquals($data['answer'], $response->answer);
        self::assertEquals($data['answerIds'], $response->answerIds);
        self::assertEquals($data['answerRank'], $response->answerRank);
        self::assertEquals($data['answerScale'], $response->answerScale);
        self::assertEquals($data['ageGroup'], $response->ageGroup);
        self::assertEquals($data['employment'], $response->employment);
        self::assertEquals($data['gender'], $response->gender);
        self::assertEquals($data['race'], $response->race);
        self::assertEquals($data['lsmGroup'], $response->lsmGroup);
        self::assertEquals($data['boundTime'], $response->boundTime);
        self::assertEquals($data['boundDate'], $response->boundDate);

        self::assertNull($response->createdAt);

        $actualData = $response->toArray();

        self::assertEquals($data, $actualData);
    }

    public function testBuild(): void
    {
        $data = self::$responseData;

        $actual = Response::build($data, true);

        self::assertEquals($data, $actual->toArray());
    }
}
