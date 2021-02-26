<?php

namespace Tests\ApiTest\Response;

use Survey54\Library\Domain\Values\AgeGroup;
use Survey54\Library\Domain\Values\Employment;
use Survey54\Library\Domain\Values\Gender;
use Survey54\Library\Domain\Values\Race;
use Tests\ApiTest\AbstractTestCase;

class PostResponsesSetupTest extends AbstractTestCase
{
    public function testPostResponsesSetup_withMetaData(): string
    {
        $permissions = json_encode(['reap' => ['responses' => ['+create' => []]]], JSON_THROW_ON_ERROR);

        $options         = $this->tokenOption($permissions);
        $options['json'] = [
            'surveyId'     => $this->responseSurveyId,
            'respondentId' => null,
            "isCint"       => false,
            "metadata"     => [
                "mobile"     => "+447879981812",
                "ipAddress"  => null,
                "ageGroup"   => AgeGroup::AGE_16_17,
                "gender"     => Gender::MALE,
                "employment" => Employment::SELF_EMPLOYED,
                "race"       => Race::BLACK,
            ],
        ];

        $response = $this->app->post('/responses/setup', $options);
        $data     = $this->getData($response);

        self::assertEquals(202, $response->getStatusCode());
        self::assertArrayHasKey('accessToken', $data);
        self::assertArrayHasKey('nextQuestion', $data);

        return $data['respondentId'];
    }

    /**
     * @depends testPostResponsesSetup_withMetaData
     */
    public function testPostResponsesSetup_withoutMetaData($respondentId): void
    {
        $permissions = json_encode(['reap' => ['responses' => ['+create' => []]]], JSON_THROW_ON_ERROR);

        $options         = $this->tokenOption($permissions);
        $options['json'] = [
            'surveyId'     => $this->responseSurveyId,
            'respondentId' => $respondentId,
            "questionId"   => 1,
            "answer"       => null,
            "answerIds"    => [1],
            "answerScale"  => null,
            "isCint"       => false,
        ];

        $response = $this->app->post('/responses', $options);
        $data     = $this->getData($response);

        self::assertEquals(202, $response->getStatusCode());
        self::assertArrayHasKey('nextQuestion', $data);
        self::assertArrayHasKey('surveyId', $data);
    }

    public function testPostCreateUser_existingAccount(): void
    {
        $options['headers'] = [
            'Authorization' => self::$respondentData['password'],
        ];
        $options['json']    = [
            "firstName"  => self::$respondentData['firstName'],
            "lastName"   => self::$respondentData['lastName'],
            "mobile"     => "+447879981812",
            "email"      => "test_" . self::$respondentData['email'],
            "ageGroup"   => self::$respondentData['ageGroup'],
            "employment" => self::$respondentData['employment'],
            "gender"     => self::$respondentData['gender'],
            "country"    => self::$respondentData['country'],
            "region"     => self::$respondentData['region'],
        ];

        $response = $this->app->post('/respondents', $options);

        self::assertEquals(202, $response->getStatusCode());
    }
}
