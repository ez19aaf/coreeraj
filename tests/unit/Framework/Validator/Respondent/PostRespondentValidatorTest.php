<?php

namespace Tests\Unit\Framework\Validator\Respondent;

use Survey54\Reap\Framework\Validator\Respondent\PostRespondentValidator;
use Tests\Unit\AbstractTestCase;

class PostRespondentValidatorTest extends AbstractTestCase
{
    protected PostRespondentValidator $postRespondentValidator;

    protected function setUp(): void
    {
        parent::setUp();

        $this->postRespondentValidator = new PostRespondentValidator();
    }

    public function testValidate(): void
    {
        $data = [
            'email'       => self::$respondentData['email'],
            'mobile'      => self::$respondentData['mobile'],
            'dateOfBirth' => self::$respondentData['dateOfBirth'],
            'gender'      => self::$respondentData['gender'],
            'employment'  => self::$respondentData['employment'],
            'country'     => self::$respondentData['country'],
            'region'      => self::$respondentData['region'],
            'firstName'   => self::$respondentData['firstName'],
            'lastName'    => self::$respondentData['lastName'],
            'race'        => self::$respondentData['race'],
        ];

        $this->request->method('getParsedBody')
            ->willReturn($data);

        $actual = $this->postRespondentValidator->validate($this->request);

        self::assertEquals($data, $actual);
    }
}
