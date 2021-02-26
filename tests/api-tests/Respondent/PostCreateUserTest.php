<?php

namespace Tests\ApiTest\Respondent;

use Tests\ApiTest\AbstractTestCase;

class PostCreateUserTest extends AbstractTestCase
{
    public function testPostCreateUser(): void
    {
        $options['headers'] = [
            'Authorization' => self::$respondentData['password'],
        ];
        $options['json']    = [
            "firstName"      => self::$respondentData['firstName'],
            "lastName"       => self::$respondentData['lastName'],
            "mobile"         => self::$respondentData['mobile'],
            "email"          => self::$respondentData['email'],
            "ageGroup"       => self::$respondentData['ageGroup'],
            "employment"     => self::$respondentData['employment'],
            "gender"         => self::$respondentData['gender'],
            "country"        => self::$respondentData['country'],
            "region"         => self::$respondentData['region'],
            "signedUpSource" => self::$respondentData['signedUpSource'],
        ];

        $response = $this->app->post('/respondents', $options);

        self::assertEquals(202, $response->getStatusCode());
    }

    public function testPostCreateUser_alreadyExist(): void
    {
        $options['headers'] = [
            'Authorization' => self::$respondentData['password'],
        ];
        $options['json']    = [
            "firstName"  => self::$respondentData['firstName'],
            "lastName"   => self::$respondentData['lastName'],
            "mobile"     => self::$respondentData['mobile'],
            "email"      => self::$respondentData['email'],
            "ageGroup"   => self::$respondentData['ageGroup'],
            "employment" => self::$respondentData['employment'],
            "gender"     => self::$respondentData['gender'],
            "country"    => self::$respondentData['country'],
            "region"     => self::$respondentData['region'],
        ];

        $response = $this->app->post('/respondents', $options);

        self::assertEquals(400, $response->getStatusCode());
    }

    public function testPostUserVerifyMobile(): void
    {
        $options['json'] = [
            "type"             => "MOBILE",
            "value"            => self::$respondentData['mobile'],
            "verificationCode" => self::$respondentData['verificationCode'],
        ];

        $response = $this->app->post('/respondents/verify', $options);
        $data     = $this->getData($response);
        self::assertEquals(202, $response->getStatusCode());

        $GLOBALS['respondent']['uuid'] = $data['userId'];
    }
}
