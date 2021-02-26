<?php


namespace Tests\Unit\Framework\Validator\Survey;


use Exception;
use Survey54\Reap\Framework\Validator\Survey\PostAirtimeCsvValidator;
use Tests\Unit\AbstractTestCase;

class PostAirtimeCsvValidatorTest extends AbstractTestCase
{
    protected PostAirtimeCsvValidator $postAirtimeCsvValidator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->postAirtimeCsvValidator = new PostAirtimeCsvValidator();
    }

    public function testWillReturnData(): void
    {
        $data = self::$airTimeCsvData;
        $this->request->method('getParsedBody')
            ->willReturn($data);
        $actual = $this->postAirtimeCsvValidator->validate($this->request);
        self::assertEquals($actual, $data);
    }

    public function testEmptyNumberList(): void
    {
        $data = self::$airTimeCsvData;
        $this->expectException(Exception::class);
        $this->expectExceptionCode(400);
        $data['numbers'] = [];
        $this->request->method('getParsedBody')
            ->willReturn($data);
        $this->postAirtimeCsvValidator->validate($this->request);
    }

    public function testInvalidCountryNumber(): void
    {
        $data = self::$airTimeCsvData;
        $this->expectExceptionCode(400);
        $this->expectException(Exception::class);
        $data['numbers'][] = '+256871717271';
        $this->request->method('getParsedBody')
            ->willReturn($data);
        $this->postAirtimeCsvValidator->validate($this->request);
    }
}