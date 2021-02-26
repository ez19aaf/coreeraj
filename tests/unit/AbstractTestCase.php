<?php

namespace Tests\Unit;

use Dotenv\Dotenv;
use Exception;
use GuzzleHttp\Client;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Slim\Http\Request;
use stdClass;
use Survey54\Library\Adapter\CloudinaryAdapter;
use Survey54\Library\Adapter\IpToCountryAdapter;
use Survey54\Library\Domain\Values\AgeGroup;
use Survey54\Library\Domain\Values\AuthStatus;
use Survey54\Library\Domain\Values\Country;
use Survey54\Library\Domain\Values\Employment;
use Survey54\Library\Domain\Values\Gender;
use Survey54\Library\Domain\Values\GroupType;
use Survey54\Library\Domain\Values\IntegrationType;
use Survey54\Library\Domain\Values\LsmGroup;
use Survey54\Library\Domain\Values\Race;
use Survey54\Library\Domain\Values\Recurrence;
use Survey54\Library\Domain\Values\RespondentSurveyStatus;
use Survey54\Library\Domain\Values\SignedUpSource;
use Survey54\Library\Domain\Values\SurveyPushTo;
use Survey54\Library\Domain\Values\SurveyStatus;
use Survey54\Library\Domain\Values\SurveyType;
use Survey54\Library\Domain\Values\UserStatus;
use Survey54\Library\Domain\Values\UserType;
use Survey54\Library\Domain\Values\VerificationType;
use Survey54\Library\Message\MessageService;
use Survey54\Library\Message\TextMessageService;
use Survey54\Library\Repository\AdapterInterface;
use Survey54\Reap\Application\AppReviewService;
use Survey54\Reap\Application\FileService;
use Survey54\Reap\Application\GdprService;
use Survey54\Reap\Application\GhostService;
use Survey54\Reap\Application\GroupService;
use Survey54\Reap\Application\InsightService;
use Survey54\Reap\Application\OpenService;
use Survey54\Reap\Application\Repository\AirtimeCsvRepository;
use Survey54\Reap\Application\Repository\GdprRepository;
use Survey54\Reap\Application\Repository\GhostRepository;
use Survey54\Reap\Application\Repository\GroupRepository;
use Survey54\Reap\Application\Repository\InsightRepository;
use Survey54\Reap\Application\Repository\LogRepository;
use Survey54\Reap\Application\Repository\RespondentRepository;
use Survey54\Reap\Application\Repository\RespondentSurveyRepository;
use Survey54\Reap\Application\Repository\ResponseRepository;
use Survey54\Reap\Application\Repository\SurveyRepository;
use Survey54\Reap\Application\RespondentService;
use Survey54\Reap\Application\ResponseService;
use Survey54\Reap\Application\SurveyService;
use Survey54\Reap\Domain\AirtimeLogsCsv;
use Survey54\Reap\Domain\Gdpr;
use Survey54\Reap\Domain\Ghost;
use Survey54\Reap\Domain\Group;
use Survey54\Reap\Domain\Insight;
use Survey54\Reap\Domain\Log;
use Survey54\Reap\Domain\Respondent;
use Survey54\Reap\Domain\RespondentSurvey;
use Survey54\Reap\Domain\Response;
use Survey54\Reap\Domain\Survey;
use Survey54\Reap\Framework\Adapter\AfricaTalkingAdapter;
use Survey54\Reap\Framework\Adapter\AirtimeAdapter;

define('CURRENCY', 'USD');
define('ROOT_DIR', __DIR__ . '/../..');
define('LSM_RECORD_JSON', realpath(ROOT_DIR . '/src/Application/assets/json/lsm-record.json'));

abstract class AbstractTestCase extends TestCase
{
    protected AdapterInterface $adapter;
    protected AirtimeAdapter $airtimeAdapter;
    protected IpToCountryAdapter $ipToCountryAdapter;

    protected Gdpr $gdpr;
    protected GdprRepository $gdprRepository;
    protected GdprService $gdprService;

    protected Insight $insight;
    protected InsightRepository $insightRepository;
    protected InsightService $insightService;

    protected FileService $fileService;

    protected Ghost $ghost;
    protected GhostRepository $ghostRepository;
    protected GhostService $ghostService;

    protected Log $log;
    protected LogRepository $logRepository;

    protected OpenService $openService;

    protected Request $request;

    protected Respondent $respondent;
    protected RespondentRepository $respondentRepository;
    protected RespondentService $respondentService;

    protected RespondentSurvey $respondentSurvey;
    protected RespondentSurvey $respondentSurveyRedeemed;
    protected RespondentSurveyRepository $respondentSurveyRepository;

    protected Response $response;
    protected Response $responseScale;
    protected ResponseRepository $responseRepository;
    protected ResponseService $responseService;

    protected Survey $survey;
    protected SurveyRepository $surveyRepository;
    protected SurveyService $surveyService;

    protected Group $group;
    protected GroupRepository $groupRepository;
    protected GroupService $groupService;

    protected TextMessageService $textMessageService;
    protected MessageService $messageService;

    protected AirtimeCsvRepository $airtimeCsvRepository;
    protected AirtimeLogsCsv       $airtimeLogsCsv;

    protected CloudinaryAdapter $imageAdapter;
    protected AfricaTalkingAdapter $africasTalkingAdapter;

    protected AppReviewService $appReviewService;

    protected Client $client;

    protected function setUp(): void
    {
        parent::setUp();

        $env = Dotenv::createImmutable(realpath(__DIR__ . '/../..'));
        $env->load();

        $_SERVER['SWITCH_AIRTIME'] = 'ON';

        // Declare future env vars as globals
        $GLOBALS['CHARACTERS'] = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';

        $this->client = $this->createMock(Client::class);

        $this->appReviewService = $this->createMock(AppReviewService::class);

        $this->adapter            = $this->createMock(AdapterInterface::class);
        $this->airtimeAdapter     = $this->createMock(AirtimeAdapter::class);
        $this->ipToCountryAdapter = $this->createMock(IpToCountryAdapter::class);

        $this->gdpr           = Gdpr::build(self::$gdprData);
        $this->gdprRepository = $this->createMock(GdprRepository::class);
        $this->gdprService    = $this->createMock(GdprService::class);

        $this->group           = Group::build(self::$groupData);
        $this->groupService   = $this->createMock(GroupService::class);
        $this->groupRepository = $this->createMock(GroupRepository::class);

        $this->ghost           = Ghost::build(self::$ghostData);
        $this->ghostRepository = $this->createMock(GhostRepository::class);
        $this->ghostService    = $this->createMock(GhostService::class);

        $this->insight           = Insight::build(self::$insightData);
        $this->insightRepository = $this->createMock(InsightRepository::class);
        $this->insightService    = $this->createMock(InsightService::class);

        $this->log           = Log::build(self::$logData);
        $this->logRepository = $this->createMock(LogRepository::class);

        $this->openService = $this->createMock(OpenService::class);

        $this->fileService = $this->createMock(FileService::class);

        $this->request = $this->createMock(Request::class);

        $this->respondent           = Respondent::build(self::$respondentData);
        $this->respondentRepository = $this->createMock(RespondentRepository::class);
        $this->respondentService    = $this->createMock(RespondentService::class);

        $this->respondentSurvey           = RespondentSurvey::build(self::$respondentSurveyData);
        $this->respondentSurveyRedeemed   = RespondentSurvey::build(self::$respondentSurveyRedeemedData);
        $this->respondentSurveyRepository = $this->createMock(RespondentSurveyRepository::class);

        $this->response           = Response::build(self::$responseData);
        $this->responseScale      = Response::build(self::$responseDataScale);
        $this->responseRepository = $this->createMock(ResponseRepository::class);
        $this->responseService    = $this->createMock(ResponseService::class);

        $this->survey           = Survey::build(self::$surveyData);
        $this->surveyRepository = $this->createMock(SurveyRepository::class);
        $this->surveyService    = $this->createMock(SurveyService::class);

        $this->airtimeLogsCsv       = AirtimeLogsCsv::build(self::$airtimeLog);
        $this->airtimeCsvRepository = $this->createMock(AirtimeCsvRepository::class);

        $this->textMessageService    = $this->createMock(TextMessageService::class);
        $this->messageService        = $this->createMock(MessageService::class);
        $this->imageAdapter          = $this->createMock(CloudinaryAdapter::class);
        $this->africasTalkingAdapter = $this->createMock(AfricaTalkingAdapter::class);
    }

    protected function client(string $method, ?array $response = null, int $code = 200): MockObject
    {
        $mockResponse = $this->getMockBuilder(stdClass::class)
            ->addMethods(['getBody', 'getStatusCode'])
            ->getMock();
        $mockResponse->method('getBody')
            ->willReturn(json_encode($response, JSON_THROW_ON_ERROR));
        $mockResponse->method('getStatusCode')
            ->willReturn($code);

        $clientMock = $this->getMockBuilder(Client::class)
            ->addMethods([$method])
            ->getMock();
        $clientMock->method($method)
            ->willReturn($mockResponse);

        if ($code === 500) {
            $clientMock->method($method)
                ->willThrowException(new Exception());
        }

        return $clientMock;
    }

    protected static array $cintData = [
        'uuid'            => 'project_id',
        'name'            => 'name',
        'tentativePayout' => 'tentative_payout',
        'country'         => 'country',
        'loi'             => 1,
        'cpi'             => 1,
        'delayCrediting'  => 'delay_crediting',
        'matchToQualify'  => 'match_to_qualify',
        'metadata'        => ['metadata'],
        'createdAt'       => null,
        'updatedAt'       => null,
    ];

    protected static array $gdprData = [
        'uuid'      => 'xx.xx.xxx',
        'userId'    => 'yy.yy.yyy',
        'userType'  => UserType::RESPONDENT,
        'action'    => 'DELETE_ACCOUNT',
        'duration'  => 30,
        'createdAt' => null,
        'updatedAt' => null,
    ];

    protected static array $ghostData = [
        'uuid'           => 'xx.xx.xxx',
        'ghostMobile'    => '+441234567890',
        'mobile'         => '+27123400000',
        'organisationId' => 'yy.yy.yyy',
        'createdAt'      => null,
        'updatedAt'      => null,
    ];

    protected static array $insightData = [
        'uuid'      => 'xx.xx.xxx',
        'userId'    => 'yy.yy.yyy',
        'surveyId'  => 'a3582846-9dbb-49b9-8c2c-7ced15f55816',
        'summary'   => 'This is the first summary',
        'createdAt' => null,
        'updatedAt' => null,
    ];

    protected static array $logData = [
        'uuid'       => 'xx.xx.xxx',
        'objectId'   => 'yy.yy.yyy',
        'objectType' => Survey::class,
        'action'     => 'PushSurveyToNonActivated',
        'request'    => [
            'number' => '+23408061234567',
        ],
        'response'   => [
            'success' => true,
        ],
        'createdAt'  => null,
        'updatedAt'  => null,
    ];

    protected static array $redemptionData = [
        'uuid'           => 'xx.xx.xxx',
        'amountToRedeem' => 100,
        'history'        => '[]',
        'createdAt'      => null,
        'updatedAt'      => null,
    ];

    protected static array $groupCsvData = [
        'userId' => 'yy.yy.yyy',
        'groupName'  => 'test-group',
        'groupCsv'  => [
            [
              'firstName'   => 'Samuel',
              'lastName'    => 'xyz',
              'mobile'      => '447878422345',
              "region"      => 'Londons',
            ],
            [
                'firstName' => 'Samuel',
                'lastName' => 'xyz',
                'mobile' => '4487993280815',
                'region' => 'Africa',
            ],
        ]
    ];

    protected static array $groupData = [
        'uuid'          => 'xx.xx.xxx',
        'userId'        => 'yy.yy.yyy',
        'groupName'     => "test group",
        'audience'      => [],
        'createdAt'     => null,
        'updatedAt'     => null,
        'recurrence'    => Recurrence::MONTHLY,
        'groupType'     => GroupType::DEMOGRAPHIC
    ];

    protected static array $groupSearchData = [
        'uuid'          => 'xx.xx.xxx',
        'userId'        => 'yy.yy.yyy',
        'groupName'     => "test group",
        'country'       => "Ghana",
        'lsmGroup'      =>  [],
        'race'          => [],
        'ageGroup'      => [
            AgeGroup::AGE_16_17,
            AgeGroup::AGE_18_24,
            AgeGroup::AGE_25_34,
            AgeGroup::AGE_35_44,
            AgeGroup::AGE_55_PLUS
        ],
        'gender'        => [
            Gender::FEMALE,
            Gender::MALE
        ],
        'employment'    => [
            Employment::EMPLOYED,
            Employment::SELF_EMPLOYED,
            Employment::UNEMPLOYED
        ],
        'quantity'      => 50,
        'recurrence'    => Recurrence::MONTHLY,
        'groupType'     => GroupType::DEMOGRAPHIC
    ];

    protected static array $respondentData = [
        'uuid'                    => 'xx.xx.xxx',
        'email'                   => 'test@test.com',
        'mobile'                  => '+27100000001',
        'dateOfBirth'             => '18-03-2004',
        'ageGroup'                => AgeGroup::AGE_16_17,
        'gender'                  => Gender::FEMALE,
        'employment'              => Employment::EMPLOYED,
        'country'                 => Country::SOUTH_AFRICA,
        'region'                  => 'Pretoria',
        'ipAddress'               => '192.168.0.1',
        'profileImage'            => null,
        'userStatus'              => UserStatus::DEACTIVATED,
        'authStatus'              => AuthStatus::VERIFIED,
        'password'                => 'Test.123!',
        'action'                  => null,
        'verificationCode'        => '123',
        'verificationType'        => VerificationType::EMAIL,
        'verificationExpiry'      => null,
        'verificationRetries'     => 0,
        'refreshToken'            => 'test',
        'refreshTokenExpiry'      => 'test',
        'loginAttempts'           => 0,
        'firstName'               => 'test',
        'lastName'                => 'account',
        'race'                    => Race::BLACK,
        'demographicCompleted'    => true,
        'lsm'                     => null,
        'lsmGroup'                => LsmGroup::LSM_1_4,
        'signedUpSource'          => SignedUpSource::MOBILE,
        'convertedFromOpenSurvey' => true,
        'markedForDeletion'       => false,
        'isSample'                => false,
        'isGhost'                 => false,
        'ghostMobile'             => null,
        'promptReview'            => false,
        'createdAt'               => null,
        'updatedAt'               => null,
    ];

    protected static array $respondentSurveyData = [
        'uuid'            => 'xx.xx.xxx',
        'respondentId'    => 'xx.xx.xxx',
        'surveyId'        => 'a3582846-9dbb-49b9-8c2c-7ced15f55816',
        'status'          => RespondentSurveyStatus::COMPLETED,
        'nextQuestionId'  => 1,
        'ipAddress'       => '192.168.0.1',
        'redeemed'        => false,
        'proof'           => null,
        'errored'         => false,
        'error'           => null,
        'gotoMap'         => null,
        'entryLink'       => null,
        'integrationType' => IntegrationType::NONE,
        'createdAt'       => null,
        'updatedAt'       => null,
    ];

    protected static array $respondentSurveyRedeemedData = [
        'uuid'           => 'xx.xx.xxx',
        'respondentId'   => 'xx.xx.xxx',
        'surveyId'       => 'a3582846-9dbb-49b9-8c2c-7ced15f55816',
        'status'         => RespondentSurveyStatus::COMPLETED,
        'nextQuestionId' => 1,
        'ipAddress'      => '192.168.0.1',
        'redeemed'       => true,
        'proof'          => null,
        'errored'        => false,
        'error'          => null,
        'createdAt'      => null,
        'updatedAt'      => null,
    ];

    protected static array $responseData = [
        'uuid'         => 'xx.xx.xxx',
        'respondentId' => 'yy.yy.yyy',
        'surveyId'     => 'a3582846-9dbb-49b9-8c2c-7ced15f55816',
        'questionId'   => 1,
        'goto'         => null,
        'answer'       => null,
        'answerIds'    => [1],
        'answerRank'   => null,
        'answerScale'  => null,
        'ageGroup'     => AgeGroup::AGE_16_17,
        'gender'       => Gender::FEMALE,
        'employment'   => Employment::EMPLOYED,
        'race'         => Race::BLACK,
        'lsmGroup'     => LsmGroup::LSM_1_4,
        'boundTime'    => null,
        'boundDate'    => null,
        'createdAt'    => null,
        'updatedAt'    => null,
    ];
    protected static array $responseDataScale = [
        'uuid'         => 'xx.xx.xxx',
        'respondentId' => 'yy.yy.yyy',
        'surveyId'     => 'a3582846-9dbb-49b9-8c2c-7ced15f55816',
        'questionId'   => 2,
        'goto'         => null,
        'answer'       => 5,
        'answerIds'    => [1],
        'answerRank'   => null,
        'answerScale'  => 10,
        'metadata'     => [
            'dateOfBirth' => '20-07-2000',
            'gender'      => Gender::FEMALE,
            'employment'  => Employment::EMPLOYED,
            'race'        => Race::BLACK,
        ],
        'createdAt'    => null,
        'updatedAt'    => null,
    ];

    protected static array $surveyData = [
        'uuid'                    => 'ab3163e8-73a3-42d2-90e8-09d32bd52410',
        'userId'                  => 'a16a9a79-5f05-4b22-8b2e-a2d017a9adc0',
        'title'                   => 'Sample Survey',
        'description'             => 'This is a description',
        'type'                    => SurveyType::WEB,
        'expectedCompletes'       => 100,
        'actualCompletes'         => 0,
        'actualCompletesPercent'  => 0,
        'countries'               => [Country::SOUTH_AFRICA],
        'sample'                  => [
            'ageGroup'   => [AgeGroup::AGE_16_17, AgeGroup::AGE_18_24, AgeGroup::AGE_25_34, AgeGroup::AGE_35_44],
            'gender'     => [Gender::MALE, Gender::FEMALE],
            'employment' => [Employment::EMPLOYED, Employment::SELF_EMPLOYED, Employment::UNEMPLOYED],
            'race'       => [Race::BLACK, Race::WHITE],
            'lsmGroup'   => [LsmGroup::LSM_1_4, LsmGroup::LSM_5_6, LsmGroup::LSM_7_8, LsmGroup::LSM_9_10],
        ],
        'questions'               => [
            [
                'id'      => 1,
                'text'    => 'What is your favourite food?',
                'type'    => 'MULTIPLE_CHOICE',
                'media'   => [
                    'type'       => 'VIDEO',
                    'resource'   => 'orgId_surId_qId.mp4',
                    'supplement' => false,
                ],
                'options' => [
                    [
                        'id'   => 1,
                        'text' => 'Rice',
                        'goto' => null,
                    ],
                    [
                        'id'   => 2,
                        'text' => 'Potato',
                        'goto' => null,
                    ],
                ],
            ],
            [
                'id'    => 2,
                'text'  => 'What is your favourite recipe book?',
                'type'  => 'OPEN_ENDED',
                'media' => [
                    'type'       => 'NONE',
                    'resource'   => null,
                    'supplement' => false,
                ],
                'goto'  => 0,
                'keywords' => [],
            ],
            [
                'id'    => 3,
                'type'  => 'SCALE',
                'text'  => 'How likely is it that you would recommend us to a friend?',
                'scale' => [
                    'from'     => 0,
                    'fromName' => 'Not at all likely',
                    'to'       => 10,
                    'toName'   => 'Extremely likely',
                ],
                'goto'  => null,
            ],
            [
                'id'      => 4,
                'text'    => 'What is your favourite dessert?',
                'type'    => 'SINGLE_CHOICE',
                'media'   => [
                    'type'       => 'VIDEO',
                    'resource'   => 'orgId_surId_qId.mp4',
                    'supplement' => false,
                ],
                'options' => [
                    [
                        'id'   => 1,
                        'text' => 'Ice cream',
                        'goto' => null,
                    ],
                    [
                        'id'   => 2,
                        'text' => 'Cake',
                        'goto' => null,
                    ],
                ],
            ],
        ],
        'image'                   => 'https://cdn.pixabay.com/photo/2016/07/10/19/19/interior-design-1508276_960_720.jpg',
        'groupId'                 => null,
        'audience'                => null,
        'tagIds'                  => [
            'da3163e8-73a3-42d2-90e8-09d32bd52220',
            'da3163e8-73a3-42d2-90e8-09d32bd52221',
        ],
        'tagLabels'               => [
            'Food & Drinks',
            'Health',
        ],
        'favourite'               => false,
        'status'                  => SurveyStatus::INACTIVE,
        'orderId'                 => '12345',
        'countScreeningQuestions' => 0,
        'incidentRate'            => 0,
        'lengthOfInterview'       => 0,
        'incentive'               => 100,
        'incentiveCurrency'       => 'NGN',
        'smsCode'                 => null,
        'ussdCode'                => null,
        'category'                => null,
        'subject'                 => null,
        'recurrence'              => null,
        'pushNotification'        => false,
        'pushTo'                  => SurveyPushTo::LIVE,
        'createdAt'               => null,
        'updatedAt'               => null,
    ];

    protected static array $airTimeCsvData = [
        'country'   => Country::KENYA,
        'incentive' => 100,
        'numbers'   => [
            '+254701020304',
            '+254701030205',
        ],
    ];
    protected static array $airtimeLog = [
        'uuid'      => 'ac8d3a64-66f5-4413-933f-d62542c34086',
        'mobile'    => '+254793421224',
        'redeemed'  => true,
        'proof'     => [
            'mobile'    => '+27123456789',
            'incentive' => '10 ZAR',
        ],
        'errored'   => true,
        'error'     => null,
        'createdAt' => null,
        'updatedAt' => null,
    ];
    protected static array $ussdData = [
        'phoneNumber' => '+447123456789',
        'text'        => '*100*1*2*2*10',
        'serviceCode' => '300',
        'sessionId'   => '1234',
    ];
    protected static array $ussdDataInvalidCode = [
        'phoneNumber' => '+447123456789',
        'text'        => 'Invalid',
        'serviceCode' => '300',
        'sessionId'   => '1234',
    ];
}
