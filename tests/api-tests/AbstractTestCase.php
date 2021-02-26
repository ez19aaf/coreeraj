<?php

namespace Tests\ApiTest;

use Dotenv\Dotenv;
use GuzzleHttp\Client;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Survey54\Library\Domain\Values\AgeGroup;
use Survey54\Library\Domain\Values\AuthStatus;
use Survey54\Library\Domain\Values\Employment;
use Survey54\Library\Domain\Values\Gender;
use Survey54\Library\Domain\Values\GroupType;
use Survey54\Library\Domain\Values\Race;
use Survey54\Library\Domain\Values\Recurrence;
use Survey54\Library\Domain\Values\SignedUpSource;
use Survey54\Library\Domain\Values\UserStatus;
use Survey54\Library\Domain\Values\VerificationType;
use Survey54\Library\Token\TokenBuilder;

abstract class AbstractTestCase extends TestCase
{
    protected const SERVICE = 'REAP_SERVICE';
    protected Client $app;

    protected string $responseSurveyId;
    protected string $insightSurveyId;
    protected string $userId;
    protected array $respondent;

    protected function setUp(): void
    {
        parent::setUp();

        $env = Dotenv::createImmutable(realpath(__DIR__ . '/../..'));
        $env->load();

        $this->app = new Client(['base_uri' => 'http://localhost:8082', 'http_errors' => false]); //on local 'http://reap.local:8082'

        $this->responseSurveyId = 'ab3163e8-73a3-42d2-90e8-09d32bd52411';
        $this->insightSurveyId  = 'ab3163e8-73a3-42d2-90e8-09d32bd52411';
        $this->userId           = 'a16a9a79-5f05-4b22-8b2e-a2d017a9adc0';
        $this->respondent       = [];
    }

    protected function getData(ResponseInterface $response): array
    {
        return json_decode($response->getBody(), true, 512, JSON_THROW_ON_ERROR);
    }

    protected function readJSON(string $file): array
    {
        return json_decode(file_get_contents($file), true, 512, JSON_THROW_ON_ERROR);
    }

    protected function tokenOption(string $permissions): array
    {
        return [
            'headers' => [
                'Authorization' => TokenBuilder::constructServiceToken(self::SERVICE, $permissions, $_SERVER['TOKEN_KEY'], 120),
            ],
        ];
    }

    protected static array $respondentData = [
        'uuid'                => 'xx.xx.xxx',
        'email'               => 'cadbury@mailinator.com',
        'mobile'              => '+447879981815',
        'ageGroup'            => AgeGroup::AGE_16_17,
        'gender'              => Gender::FEMALE,
        'employment'          => Employment::EMPLOYED,
        'country'             => 'Nigeria',
        'region'              => 'Port Harcourt',
        'profileImage'        => 'data:image/jpeg;base64,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',
        'userStatus'          => UserStatus::DEACTIVATED,
        'authStatus'          => AuthStatus::VERIFIED,
        'password'            => 'Test.123!',
        'action'              => null,
        'verificationCode'    => '111111',
        'verificationType'    => VerificationType::EMAIL,
        'verificationExpiry'  => null,
        'verificationRetries' => null,
        'refreshToken'        => 'test',
        'refreshTokenExpiry'  => 'test',
        'loginAttempts'       => null,
        'firstName'           => 'test',
        'lastName'            => 'account',
        'race'                => Race::ASIAN,
        'isCint'              => true,
        'createdAt'           => null,
        'updatedAt'           => null,
        'signedUpSource'      => SignedUpSource::MOBILE,
    ];

    protected static array $groupData = [
        'uuid'          => 'xx.xx.xxx',
        'groupName'     => "test group",
        'country'       => "Ghana",
        'sample'        => [
            'lsmGroup'      =>  [],
            'race'          => [],
            'ageGroup'      => [
                AgeGroup::AGE_18_24,
            ],
            'gender'        => [
                Gender::FEMALE,
                Gender::MALE
            ],
            'employment'    => [
                Employment::UNEMPLOYED
            ],
        ],
        'quantity'      => 50,
        'groupType'     => GroupType::DEMOGRAPHIC,
        'recurrence'    => Recurrence::MONTHLY
    ];
}
