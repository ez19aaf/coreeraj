<?php

namespace Tests\ApiTest\Respondent;

use Tests\ApiTest\AbstractTestCase;

class PostMobileForgotTest extends AbstractTestCase
{
    public function testUserForgotPassword_mobile(): void
    {
        $options['json'] = [
            "type"  => "MOBILE",
            "value" => self::$respondentData['mobile'],
        ];

        $response = $this->app->post('/respondents/forgot-password', $options);

        self::assertEquals(202, $response->getStatusCode());
    }


    public function testNewVerificationCodeMobile(): void
    {
        // Stop here and mark this test as incomplete.
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );

        $options['json'] = [
            "type"  => "MOBILE",
            "value" => self::$respondentData['mobile'],
        ];

        $response = $this->app->post('/respondents/send-new-verification', $options);

        self::assertEquals(202, $response->getStatusCode());
    }

    public function testPostUserVerifyMobile(): void
    {
        $options['json'] = [
            "type"             => "MOBILE",
            "value"            => self::$respondentData['mobile'],
            "verificationCode" => self::$respondentData['verificationCode'],
        ];

        $response = $this->app->post('/respondents/verify', $options);

        self::assertEquals(202, $response->getStatusCode());
    }

    public function testUserSetPassword(): void
    {
        $options['headers'] = [
            'Authorization' => self::$respondentData['password'],
        ];
        $options['json']    = [];

        $response = $this->app->post('/respondents/' . $GLOBALS['respondent']['uuid'] . '/set-password', $options);

        self::assertEquals(202, $response->getStatusCode());

        $GLOBALS['respondent'] = $this->getData($response);
    }
}
