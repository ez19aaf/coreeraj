<?php


namespace Tests\Unit\Framework\Validator\Respondent;


use Survey54\Reap\Framework\Validator\Respondent\PostCheckMobileValidator;
use Tests\Unit\AbstractTestCase;

class PostCheckMobileValidatorTest extends AbstractTestCase
{
    protected PostCheckMobileValidator $postCheckMobileValidator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->postCheckMobileValidator = new PostCheckMobileValidator();
    }

    public function testWillReturnData(): void
    {
        $data = [
            'mobile' => '+254791230230',
        ];
        $this->request->method('getParsedBody')
            ->willReturn($data);

        $actual = $this->postCheckMobileValidator->validate($this->request);

        self::assertEquals($data, $actual);
    }
}
