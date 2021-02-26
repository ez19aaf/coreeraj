<?php

namespace Tests\Unit\Framework\Validator\Response;

use PHPUnit\Framework\TestCase;
use Slim\Http\Request;
use Survey54\Library\Domain\Values\AgeGroup;
use Survey54\Library\Domain\Values\Employment;
use Survey54\Library\Domain\Values\Gender;
use Survey54\Library\Domain\Values\Race;
use Survey54\Reap\Framework\Validator\Response\PostResponseSetupValidator;

class PostResponseSetupValidatorTest extends TestCase
{
    /** @var PostResponseSetupValidator */
    private $postResponseSetupValidator;
    /** @var Request */
    private $request;

    protected function setUp(): void
    {
        parent::setUp();

        $this->postResponseSetupValidator = new PostResponseSetupValidator();
        $this->request                    = $this->createMock(Request::class);
    }

    public function testValidate(): void
    {
        $data = [
            'surveyId'     => '4223b102-877e-4907-ad16-710fc4b58d06',
            'isCint'       => true,
            'respondentId' => '38c5ca80-36bf-4c09-a349-c239b5f28202',
            'metadata'     => [
                'email'      => null,
                'mobile'     => '+27123456789',
                'country'    => 'South Africa',
                'ipAddress'  => '192.168.0.1',
                'ageGroup'   => AgeGroup::AGE_16_17,
                'employment' => Employment::EMPLOYED,
                'gender'     => Gender::MALE,
                'race'       => Race::BLACK,
                'lsmKeys'    => ['i1', 'i2', 'i3'],
            ],
        ];

        $this->request->method('getParsedBody')
            ->willReturn($data);

        $actual = $this->postResponseSetupValidator->validate($this->request);

        self::assertEquals($data, $actual);
    }
}
