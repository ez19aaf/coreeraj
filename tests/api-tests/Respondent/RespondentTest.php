<?php

namespace Tests\ApiTest\Respondent;

use Tests\ApiTest\AbstractTestCase;

class RespondentTest extends AbstractTestCase
{
    public function testGetRespondent(): void
    {
        $permissions = json_encode(['reap' => ['respondents' => ['+read' => []]]], JSON_THROW_ON_ERROR);

        $response = $this->app->get('/respondents/' . $GLOBALS['respondent']['uuid'], $this->tokenOption($permissions));

        self::assertEquals(200, $response->getStatusCode());
    }

    public function testPostUpdateUser(): void
    {
        $permissions = json_encode(['reap' => ['respondents' => ['+update' => []]]], JSON_THROW_ON_ERROR);

        $options         = $this->tokenOption($permissions);
        $options['json'] = [
            "firstName"  => self::$respondentData['firstName'],
            "lastName"   => self::$respondentData['lastName'],
            "ageGroup"   => self::$respondentData['ageGroup'],
            "employment" => self::$respondentData['employment'],
            "gender"     => self::$respondentData['gender'],
            "race"       => self::$respondentData['race'],
            "region"     => self::$respondentData['region'],
        ];

        $response = $this->app->put('/respondents/' . $GLOBALS['respondent']['uuid'], $options);

        self::assertEquals(200, $response->getStatusCode());
    }

    public function testUpdateProfileImageUser(): void
    {
        $permissions = json_encode(['reap' => ['respondents' => ['+update' => []]]], JSON_THROW_ON_ERROR);

        $options         = $this->tokenOption($permissions);
        $options['json'] = [
            "image" => self::$respondentData['profileImage'],
        ];

        $response = $this->app->put('/respondents/' . $GLOBALS['respondent']['uuid'] . '/upload-photo', $options);

        self::assertEquals(202, $response->getStatusCode());
    }

    public function testRemoveProfileImageUser(): void
    {
        $permissions = json_encode(['reap' => ['respondents' => ['+update' => []]]], JSON_THROW_ON_ERROR);

        $options         = $this->tokenOption($permissions);
        $options['json'] = [];

        $response = $this->app->delete('/respondents/' . $GLOBALS['respondent']['uuid'] . '/remove-photo', $options);

        self::assertEquals(202, $response->getStatusCode());
    }

    public function testChangePassword(): void
    {
        $permissions = json_encode(['reap' => ['respondents' => ['+update' => []]]], JSON_THROW_ON_ERROR);

        $options         = $this->tokenOption($permissions);
        $options['json'] = [
            'oldPassword' => self::$respondentData['password'],
            'password'    => self::$respondentData['password'],
        ];

        $response = $this->app->put('/respondents/' . $GLOBALS['respondent']['uuid'] . '/change-password', $options);

        self::assertEquals(202, $response->getStatusCode());
    }

    public function testGetSurveyHistory(): void
    {
        $permissions = json_encode(['reap' => ['respondents' => ['+read' => []]]], JSON_THROW_ON_ERROR);

        $response = $this->app->get('/respondents/' . $GLOBALS['respondent']['uuid'] . '/survey-history', $this->tokenOption($permissions));

        self::assertEquals(200, $response->getStatusCode());
    }

    public function testGetTotalEarnings(): void
    {
        $permissions = json_encode(['reap' => ['respondents' => ['+read' => []]]], JSON_THROW_ON_ERROR);

        $response = $this->app->get('/respondents/' . $GLOBALS['respondent']['uuid'] . '/total-earnings', $this->tokenOption($permissions));

        self::assertEquals(200, $response->getStatusCode());
    }

    public function testDeleteRespondent(): void
    {
        $permissions = json_encode(['reap' => ['respondents' => ['+delete' => []]]], JSON_THROW_ON_ERROR);

        $options         = $this->tokenOption($permissions);
        $options['json'] = [];

        $response = $this->app->delete('/respondents/' . $GLOBALS['respondent']['uuid'], $options);

        self::assertEquals(202, $response->getStatusCode());

        unset($GLOBALS['respondent']);
    }
}
