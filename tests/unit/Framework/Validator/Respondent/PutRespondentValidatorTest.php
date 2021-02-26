<?php

namespace Tests\Unit\Framework\Validator\Respondent;

use Survey54\Reap\Framework\Validator\Respondent\PutRespondentValidator;
use Tests\Unit\AbstractTestCase;

class PutRespondentValidatorTest extends AbstractTestCase
{
    protected PutRespondentValidator $putRespondentValidator;

    protected function setUp(): void
    {
        parent::setUp();

        $this->putRespondentValidator = new PutRespondentValidator();
    }

    public function testValidate(): void
    {
        $data = [
            'firstName'   => self::$respondentData['firstName'],
            'lastName'    => self::$respondentData['lastName'],
            'dateOfBirth' => self::$respondentData['dateOfBirth'],
            'gender'      => self::$respondentData['gender'],
            'employment'  => self::$respondentData['employment'],
            'region'      => self::$respondentData['region'],
            'race'        => self::$respondentData['race'],
        ];

        $this->request->method('getParsedBody')
            ->willReturn($data);

        $actual = $this->putRespondentValidator->validate($this->request);

        self::assertEquals($data, $actual);
    }
}
