<?php

namespace Tests\ApiTest\Respondent;

use Tests\ApiTest\AbstractTestCase;

class PostEmailForgotTest extends AbstractTestCase
{
    public function testUserForgotPassword_email(): void
    {
        // Stop here and mark this test as incomplete.
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );

        $options['json'] = [
            "type"  => "EMAIL",
            "value" => self::$respondentData['email'],
        ];

        $response = $this->app->post('/respondents/forgot-password', $options);

        self::assertEquals(202, $response->getStatusCode());
    }

    public function testNewVerificationCodeEmail(): void
    {
        // Stop here and mark this test as incomplete.
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );

        $options['json'] = [
            "type"  => "EMAIL",
            "value" => self::$respondentData['email'],
        ];

        $response = $this->app->post('/respondents/send-new-verification', $options);

        self::assertEquals(202, $response->getStatusCode());
    }

    public function testPostUserVerifyEmail(): void
    {
        // Stop here and mark this test as incomplete.
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );

        $options['json'] = [
            "type"             => "EMAIL",
            "value"            => self::$respondentData['email'],
            "verificationCode" => self::$respondentData['verificationCode'],
        ];

        $response = $this->app->post('/respondents/verify', $options);

        self::assertEquals(202, $response->getStatusCode());
    }

    public function testUserSetPassword(): void
    {
        // Stop here and mark this test as incomplete.
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );

        $options['headers'] = [
            'Authorization' => self::$respondentData['password'],
        ];
        $options['json']    = [];

        $response = $this->app->post('/respondents/' . $GLOBALS['respondent']['uuid'] . '/set-password', $options);

        self::assertEquals(202, $response->getStatusCode());

        $GLOBALS['respondent'] = $this->getData($response);
    }
}
