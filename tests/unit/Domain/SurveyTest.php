<?php

namespace Tests\Unit\Domain;

use Survey54\Reap\Domain\Survey;
use Tests\Unit\AbstractTestCase;

class SurveyTest extends AbstractTestCase
{
    public function testSurvey(): void
    {
        $data = self::$surveyData;

        $survey = new Survey(
            $data['uuid'],
            $data['userId'],
            $data['title'],
        );

        $survey->description             = $data['description'];
        $survey->type                    = $data['type'];
        $survey->expectedCompletes       = $data['expectedCompletes'];
        $survey->countries               = $data['countries'];
        $survey->sample                  = $data['sample'];
        $survey->questions               = $data['questions'];
        $survey->image                   = $data['image'];
        $survey->groupId                 = $data['groupId'];
        $survey->audience                = $data['audience'];
        $survey->tagIds                  = $data['tagIds'];
        $survey->tagLabels               = $data['tagLabels'];
        $survey->favourite               = $data['favourite'];
        $survey->status                  = $data['status'];
        $survey->orderId                 = $data['orderId'];
        $survey->countScreeningQuestions = $data['countScreeningQuestions'];
        $survey->incidentRate            = $data['incidentRate'];
        $survey->lengthOfInterview       = $data['lengthOfInterview'];
        $survey->incentive               = $data['incentive'];
        $survey->incentiveCurrency       = $data['incentiveCurrency'];
        $survey->smsCode                 = $data['smsCode'];
        $survey->ussdCode                = $data['ussdCode'];
        $survey->actualCompletes         = $data['actualCompletes'];
        $survey->actualCompletesPercent  = $data['actualCompletesPercent'];
        $survey->category                = $data['category'];
        $survey->subject                 = $data['subject'];
        $survey->recurrence              = $data['recurrence'];
        $survey->pushNotification        = $data['pushNotification'];

        self::assertEquals($data['uuid'], $survey->uuid);
        self::assertEquals($data['userId'], $survey->userId);
        self::assertEquals($data['title'], $survey->title);
        self::assertEquals($data['description'], $survey->description);
        self::assertEquals($data['type'], $survey->type);
        self::assertEquals($data['expectedCompletes'], $survey->expectedCompletes);
        self::assertEquals($data['countries'], $survey->countries);
        self::assertEquals($data['sample'], $survey->sample);
        self::assertEquals($data['questions'], $survey->questions);
        self::assertEquals($data['image'], $survey->image);
        self::assertEquals($data['groupId'], $survey->groupId);
        self::assertEquals($data['audience'], $survey->audience);
        self::assertEquals($data['tagIds'], $survey->tagIds);
        self::assertEquals($data['tagLabels'], $survey->tagLabels);
        self::assertEquals($data['favourite'], $survey->favourite);
        self::assertEquals($data['status'], $survey->status);
        self::assertEquals($data['orderId'], $survey->orderId);
        self::assertEquals($data['countScreeningQuestions'], $survey->countScreeningQuestions);
        self::assertEquals($data['incidentRate'], $survey->incidentRate);
        self::assertEquals($data['lengthOfInterview'], $survey->lengthOfInterview);
        self::assertEquals($data['incentive'], $survey->incentive);
        self::assertEquals($data['incentiveCurrency'], $survey->incentiveCurrency);
        self::assertEquals($data['smsCode'], $survey->smsCode);
        self::assertEquals($data['ussdCode'], $survey->ussdCode);
        self::assertEquals($data['actualCompletes'], $survey->actualCompletes);
        self::assertEquals($data['actualCompletesPercent'], $survey->actualCompletesPercent);
        self::assertEquals($data['category'], $survey->category);
        self::assertEquals($data['subject'], $survey->subject);
        self::assertEquals($data['recurrence'], $survey->recurrence);
        self::assertEquals($data['pushNotification'], $survey->pushNotification);
        self::assertNull($survey->createdAt);
        self::assertNull($survey->updatedAt);

        $actualData = $survey->toArray();

        self::assertEquals($data, $actualData);
    }

    public function testBuild(): void
    {
        $data = self::$surveyData;

        $actual = Survey::build($data, true);

        self::assertInstanceOf(Survey::class, $actual);
    }
}
