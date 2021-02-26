<?php

namespace Tests\ApiTest\Respondent;

use Tests\ApiTest\AbstractTestCase;

class PostLoginTest extends AbstractTestCase
{
    public function testUserLogin_fail(): void
    {
        $options['headers'] = [
            'Authorization' => 'Password',
        ];

        $options['json'] = [
            "type"  => "MOBILE",
            "value" => self::$respondentData['mobile'],
        ];

        $response = $this->app->post('/respondents/login', $options);

        self::assertEquals(400, $response->getStatusCode());
    }

    public function testUserLogin_email(): void
    {
        $this->markTestIncomplete(
            'Email login is removed.'
        );

        $options['headers'] = [
            'Authorization' => self::$respondentData['password'],
        ];

        $options['json'] = [
            "type"  => "EMAIL",
            "value" => self::$respondentData['email'],
        ];

        $response = $this->app->post('/respondents/login', $options);

        self::assertEquals(202, $response->getStatusCode());

        $GLOBALS['respondent'] = $this->getData($response);
    }

    public function testUserLogin_mobile(): void
    {
        $options['headers'] = [
            'Authorization' => self::$respondentData['password'],
        ];

        $options['json'] = [
            "type"  => "MOBILE",
            "value" => self::$respondentData['mobile'],
        ];

        $response = $this->app->post('/respondents/login', $options);

        self::assertEquals(202, $response->getStatusCode());

        $GLOBALS['respondent'] = $this->getData($response);
    }

    public function testRespondentRefreshToken(): void
    {
        $options['json'] = [
            "refreshToken" => $GLOBALS['respondent']['refreshToken'],
            "expiredToken" => $GLOBALS['respondent']['accessToken'],
        ];

        $response = $this->app->post('/respondents/refresh-token', $options);

        self::assertEquals(202, $response->getStatusCode());
    }
}
