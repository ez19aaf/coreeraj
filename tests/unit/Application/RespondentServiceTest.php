<?php

namespace Tests\Unit\Application;

use Exception;
use Survey54\Library\Domain\Values\AuthAction;
use Survey54\Library\Domain\Values\AuthStatus;
use Survey54\Library\Domain\Values\Country;
use Survey54\Library\Domain\Values\Gender;
use Survey54\Library\Domain\Values\Race;
use Survey54\Library\Domain\Values\UserStatus;
use Survey54\Library\Domain\Values\VerificationType;
use Survey54\Library\Utilities\Helper;
use Survey54\Reap\Application\RespondentService;
use Survey54\Reap\Domain\Respondent;
use Tests\Unit\AbstractTestCase;

class RespondentServiceTest extends AbstractTestCase
{
    private array $data;
    private Respondent $respondentUpdate;

    protected function setUp(): void
    {
        parent::setUp();

        $this->data = self::$respondentData;

        $this->respondentUpdate = Respondent::build(self::$respondentData);

        $this->respondent->userStatus         = UserStatus::ACTIVATED;
        $this->respondent->verificationType   = VerificationType::MOBILE;
        $this->respondent->authStatus         = AuthStatus::AWAITING_VERIFICATION;
        $this->respondent->verificationExpiry = date('Y-m-d\TH:i:sP', strtotime("tomorrow"));
        $this->respondent->refreshTokenExpiry = date('Y-m-d\TH:i:sP', strtotime("tomorrow"));

        $this->respondentService = new RespondentService(
            $this->ghostRepository,
            $this->logRepository,
            $this->respondentRepository,
            $this->respondentSurveyRepository,
            $this->surveyRepository,
            $this->gdprRepository,
            $this->appReviewService,
            $this->gdprService,
            $this->openService,
            $this->textMessageService,
            $this->imageAdapter,
            $this->ipToCountryAdapter,
            'secret',
        );
    }

    public function testCreate_mobileExist(): void
    {
        $this->expectExceptionCode(400);
        $this->expectException(Exception::class);

        $this->respondentRepository->method('findByMobile')
            ->willReturn($this->respondent);

        $this->respondentService->create($this->data);
    }

    public function testCreate_emailExist(): void
    {
        $this->expectExceptionCode(400);
        $this->expectException(Exception::class);

        $this->respondentRepository->method('findByEmail')
            ->willReturn(true);

        $this->respondentService->create($this->data);
    }

    public function testCreateFromExistingProfile_byMobile(): void
    {
        $this->respondentRepository->method('findByMobile')
            ->willReturn($this->respondentUpdate);

        $this->respondentRepository->method('find')
            ->willReturn($this->respondent);

        $this->gdprRepository->method('findByUserId')
            ->willReturn($this->gdpr);

        $this->respondentRepository->method('findByEmail')
            ->willReturn(false);

        $this->ipToCountryAdapter->method('getIPCountry')
            ->willReturn(['countryName' => Country::SOUTH_AFRICA]);

        $actual = $this->respondentService->create($this->data);

        $expected = [
            'message' => 'You have been successfully registered. Please use the code sent to your mobile to complete the sign up process.',
        ];

        self::assertEquals($expected, $actual);
    }

    public function testCreate_byMobile(): void
    {
        $this->respondentRepository->method('findByEmail')
            ->willReturn(false);

        $this->ghostRepository->method('findByMobile')
            ->willReturn(false);

        $this->ipToCountryAdapter->method('getIPCountry')
            ->willReturn(['countryName' => Country::SOUTH_AFRICA]);

        $actual = $this->respondentService->create($this->data);

        $expected = [
            'message' => 'You have been successfully registered. Please use the code sent to your mobile to complete the sign up process.',
        ];

        self::assertEquals($expected, $actual);
    }

    public function testVerify_respondentNotFoundByMobile(): void
    {
        $this->expectExceptionCode(400);
        $this->expectException(Exception::class);

        $data = [
            'type'  => VerificationType::MOBILE,
            'value' => $this->respondent->mobile,
        ];
        $this->respondentService->verify($data);
    }

    public function testVerify_respondentNotFoundByEmail(): void
    {
        $this->expectExceptionCode(400);
        $this->expectException(Exception::class);

        $data = [
            'type'  => VerificationType::EMAIL,
            'value' => $this->respondent->email,
        ];
        $this->respondentService->verify($data);
    }

    public function testVerify_userDeactivated(): void
    {
        $this->expectExceptionCode(400);
        $this->expectException(Exception::class);

        $data = [
            'type'  => VerificationType::MOBILE,
            'value' => $this->respondent->mobile,
        ];

        $this->respondent->userStatus = UserStatus::DEACTIVATED;

        $this->respondentRepository->method('findByMobile')
            ->willReturn($this->respondent);

        $this->respondentService->verify($data);
    }

    public function testVerify_invalidVerificationCode(): void
    {
        $this->expectExceptionCode(400);
        $this->expectException(Exception::class);

        $data = [
            'type'             => VerificationType::MOBILE,
            'value'            => $this->respondent->mobile,
            'verificationCode' => 'test',
        ];

        $this->respondentRepository->method('findByMobile')
            ->willReturn($this->respondent);

        $this->respondentService->verify($data);
    }

    public function testVerify_invalidVerificationTypeMobile(): void
    {
        $this->expectExceptionCode(400);
        $this->expectException(Exception::class);

        $data = [
            'type'             => VerificationType::MOBILE,
            'value'            => $this->respondent->mobile,
            'verificationCode' => $this->respondent->verificationCode,
        ];

        $this->respondent->verificationType = VerificationType::EMAIL;

        $this->respondentRepository->method('findByMobile')
            ->willReturn($this->respondent);

        $this->respondentService->verify($data);
    }

    public function testVerify_invalidVerificationTypeEmail(): void
    {
        $this->expectExceptionCode(400);
        $this->expectException(Exception::class);

        $data = [
            'type'             => VerificationType::EMAIL,
            'value'            => $this->respondent->email,
            'verificationCode' => $this->respondent->verificationCode,
        ];

        $this->respondentRepository->method('findByEmail')
            ->willReturn($this->respondent);

        $this->respondentService->verify($data);
    }

    public function testVerify_awatingVerificationRequired(): void
    {
        $this->expectExceptionCode(400);
        $this->expectException(Exception::class);

        $data = [
            'type'             => VerificationType::MOBILE,
            'value'            => $this->respondent->mobile,
            'verificationCode' => $this->respondent->verificationCode,
        ];

        $this->respondent->authStatus = AuthStatus::VERIFIED;

        $this->respondentRepository->method('findByMobile')
            ->willReturn($this->respondent);

        $this->respondentService->verify($data);
    }

    public function testVerify_codeExpired(): void
    {
        $this->expectExceptionCode(400);
        $this->expectException(Exception::class);

        $data = [
            'type'             => VerificationType::MOBILE,
            'value'            => $this->respondent->mobile,
            'verificationCode' => $this->respondent->verificationCode,
        ];

        $this->respondent->verificationExpiry = date('Y-m-d\TH:i:sP', strtotime("yesterday"));

        $this->respondentRepository->method('findByMobile')
            ->willReturn($this->respondent);

        $this->respondentService->verify($data);
    }

    public function testVerify(): void
    {
        $data = [
            'type'             => VerificationType::MOBILE,
            'value'            => $this->respondent->mobile,
            'verificationCode' => $this->respondent->verificationCode,
        ];

        $this->respondentRepository->method('findByMobile')
            ->willReturn($this->respondent);

        $this->respondentRepository->method('find')
            ->willReturn($this->respondent);

        $actual = $this->respondentService->verify($data);

        $expected = [
            'userId' => $this->respondent->uuid,
        ];

        self::assertEquals($expected, $actual);
    }

    public function testVerify_withRegister(): void
    {
        $data = [
            'type'             => VerificationType::MOBILE,
            'value'            => $this->respondent->mobile,
            'verificationCode' => $this->respondent->verificationCode,
        ];

        $this->respondent->action = AuthAction::REGISTER;

        $this->respondentRepository->method('findByMobile')
            ->willReturn($this->respondent);

        $this->respondentRepository->method('find')
            ->willReturn($this->respondent);

        $actual = $this->respondentService->verify($data);

        self::assertArrayHasKey('accessToken', $actual);
        self::assertArrayHasKey('refreshToken', $actual);
        self::assertArrayHasKey('userId', $actual);
    }

    public function testSetPasswordAndLogin_userNotFound(): void
    {
        $this->expectExceptionCode(400);
        $this->expectException(Exception::class);

        $this->respondentService->setPasswordAndLogin($this->data['uuid'], $this->data['password']);
    }

    public function testSetPasswordAndLogin_userActivated(): void
    {
        $this->expectExceptionCode(400);
        $this->expectException(Exception::class);

        $this->respondent->userStatus = UserStatus::DEACTIVATED;

        $this->respondentRepository->method('find')
            ->willReturn($this->respondent);

        $this->respondentService->setPasswordAndLogin($this->data['uuid'], $this->data['password']);
    }

    public function testSetPasswordAndLogin_awatingAction(): void
    {
        $this->expectExceptionCode(400);
        $this->expectException(Exception::class);

        $this->respondentRepository->method('find')
            ->willReturn($this->respondent);

        $this->respondentService->setPasswordAndLogin($this->data['uuid'], $this->data['password']);
    }

    public function testSetPasswordAndLogin(): void
    {
        $this->respondentRepository->method('find')
            ->willReturn($this->respondent);

        $this->respondent->authStatus = AuthStatus::AWAITING_ACTION;
        $this->respondent->action     = AuthAction::FORGOT_PASSWORD;
        $this->respondent->password   = $this->respondentService->hashPassword($this->respondent->password);

        $this->respondentRepository->method('findByMobile')
            ->willReturn($this->respondent);

        $actual = $this->respondentService->setPasswordAndLogin($this->data['uuid'], $this->data['password']);

        $expected = (array)$this->respondent;

        self::assertArrayHasKey('accessToken', $actual);
        self::assertArrayHasKey('refreshToken', $actual);
        unset($actual['refreshToken']);
        unset($actual['accessToken']);
        unset($expected['refreshToken']);
        self::assertEquals($expected, $actual);
    }

    public function testForgotPassword_byMobile(): void
    {
        $data = [
            'type'  => VerificationType::MOBILE,
            'value' => $this->respondent->mobile,
        ];

        $this->respondent->authStatus = AuthStatus::AWAITING_ACTION;

        $this->respondentRepository->method('findByMobile')
            ->willReturn($this->respondent);

        $this->respondentRepository->method('find')
            ->willReturn($this->respondent);

        $this->respondentService->forgotPassword($data);
        self::assertTrue(true);
    }

    public function testChangeStatus(): void
    {
        $this->respondentRepository->method('find')
            ->willReturn($this->respondent);

        $this->respondentService->changeStatus($this->data['uuid'], $this->data['userStatus']);
        self::assertTrue(true);
    }

    public function testChangePassword_mismatchPassword(): void
    {
        $this->expectExceptionCode(400);
        $this->expectException(Exception::class);

        $data = [
            'uuid'        => $this->data['uuid'],
            'oldPassword' => $this->data['password'],
            'password'    => $this->data['password'],
        ];

        $this->respondentRepository->method('find')
            ->willReturn($this->respondent);

        $this->respondentService->changePassword($data);
    }

    public function testChangePassword_verifiedAuthIsRequired(): void
    {
        $this->expectExceptionCode(400);
        $this->expectException(Exception::class);

        $data = [
            'uuid'        => $this->data['uuid'],
            'oldPassword' => $this->data['password'],
            'password'    => $this->data['password'],
        ];

        $this->respondent->password = $this->respondentService->hashPassword($this->respondent->password);

        $this->respondentRepository->method('find')
            ->willReturn($this->respondent);

        $this->respondentService->changePassword($data);
    }

    public function testChangePasswordFailOnSamePassword(): void
    {
        $this->expectExceptionCode(400);
        $this->expectException(Exception::class);
        $data = [
            'uuid'        => $this->data['uuid'],
            'oldPassword' => $this->data['password'],
            'password'    => $this->data['password'],
        ];

        $this->respondentService->changePassword($data);
    }

    public function testChangePassword(): void
    {
        $data = [
            'uuid'        => $this->data['uuid'],
            'oldPassword' => $this->data['password'],
            'password'    => 'NewPassword*1',
        ];

        $this->respondent->password   = $this->respondentService->hashPassword($this->respondent->password);
        $this->respondent->authStatus = AuthStatus::VERIFIED;

        $this->respondentRepository->method('find')
            ->willReturn($this->respondent);

        $actual = $this->respondentService->changePassword($data);
        self::assertEquals($this->respondent, $actual);
    }

    public function testSendNewVerificationCode_retriesExceed(): void
    {
        $this->expectExceptionCode(400);
        $this->expectException(Exception::class);

        $data = [
            'type'                => VerificationType::MOBILE,
            'value'               => $this->respondent->mobile,
            'oldVerificationCode' => $this->respondent->verificationCode,
        ];

        $this->respondent->verificationRetries = 5;

        $this->respondentRepository->method('findByMobile')
            ->willReturn($this->respondent);

        $this->respondentService->sendNewVerificationCode($data);
    }

    public function testSendNewVerificationCode_checkCodeInDB_Email(): void
    {
        $this->expectExceptionCode(400);
        $this->expectException(Exception::class);

        $data = [
            'type'                => VerificationType::EMAIL,
            'value'               => $this->respondent->email,
            'oldVerificationCode' => '456',
        ];

        $this->respondentRepository->method('findByEmail')
            ->willReturn($this->respondent);

        $this->respondentService->sendNewVerificationCode($data);
    }

    public function testSendNewVerificationCode_verificationTypeNotMobile(): void
    {
        $this->expectExceptionCode(400);
        $this->expectException(Exception::class);

        $data = [
            'type'                => VerificationType::MOBILE,
            'value'               => $this->respondent->mobile,
            'oldVerificationCode' => $this->respondent->verificationCode,
        ];

        $this->respondent->verificationType = VerificationType::EMAIL;

        $this->respondentRepository->method('findByMobile')
            ->willReturn($this->respondent);

        $this->respondentService->sendNewVerificationCode($data);
    }

    public function testSendNewVerificationCode_verificationTypeNotEmail(): void
    {
        $this->expectExceptionCode(400);
        $this->expectException(Exception::class);

        $data = [
            'type'                => VerificationType::EMAIL,
            'value'               => $this->respondent->email,
            'oldVerificationCode' => $this->respondent->verificationCode,
        ];

        $this->respondent->verificationType = VerificationType::MOBILE;

        $this->respondentRepository->method('findByEmail')
            ->willReturn($this->respondent);

        $this->respondentService->sendNewVerificationCode($data);
    }

    public function testSendNewVerificationCode_awatingVerificationRequired(): void
    {
        $this->expectExceptionCode(400);
        $this->expectException(Exception::class);

        $data = [
            'type'                => VerificationType::MOBILE,
            'value'               => $this->respondent->mobile,
            'oldVerificationCode' => $this->respondent->verificationCode,
        ];

        $this->respondent->authStatus = AuthStatus::VERIFIED;

        $this->respondentRepository->method('findByMobile')
            ->willReturn($this->respondent);

        $this->respondentService->sendNewVerificationCode($data);
    }

    public function testSendNewVerificationCode_invalidVerificationFlow(): void
    {
        $this->expectExceptionCode(400);
        $this->expectException(Exception::class);

        $data = [
            'type'                => VerificationType::MOBILE,
            'value'               => $this->respondent->mobile,
            'oldVerificationCode' => $this->respondent->verificationCode,
        ];

        $this->respondentRepository->method('findByMobile')
            ->willReturn($this->respondent);

        $this->respondentService->sendNewVerificationCode($data);
    }

    public function testSendNewVerificationCode_withMobile(): void
    {
        $data = [
            'type'                => VerificationType::MOBILE,
            'value'               => $this->respondent->mobile,
            'oldVerificationCode' => $this->respondent->verificationCode,
        ];

        $this->respondent->action = AuthAction::REGISTER;

        $this->respondentRepository->method('findByMobile')
            ->willReturn($this->respondent);
        $this->respondentRepository->method('find')
            ->willReturn($this->respondent);

        $actual = $this->respondentService->sendNewVerificationCode($data);

        $expected = [
            'flowType' => $this->respondent->action,
        ];

        self::assertEquals($expected, $actual);
    }

    public function testSendNewVerificationCode_withMobile_actionForgetPassword(): void
    {
        $data = [
            'type'                => VerificationType::MOBILE,
            'value'               => $this->respondent->mobile,
            'oldVerificationCode' => $this->respondent->verificationCode,
        ];

        $this->respondent->action = AuthAction::FORGOT_PASSWORD;

        $this->respondentRepository->method('findByMobile')
            ->willReturn($this->respondent);
        $this->respondentRepository->method('find')
            ->willReturn($this->respondent);

        $actual = $this->respondentService->sendNewVerificationCode($data);

        $expected = [
            'flowType' => $this->respondent->action,
        ];
        self::assertEquals($expected, $actual);
    }

    public function testUpdateDetails(): void
    {
        $data = [
            'uuid'        => $this->respondent->uuid,
            'firstName'   => $this->respondent->firstName,
            'lastName'    => $this->respondent->lastName,
            'email'       => $this->respondent->email,
            'dateOfBirth' => $this->respondent->dateOfBirth,
            'employment'  => $this->respondent->employment,
            'gender'      => $this->respondent->gender,
            'race'        => $this->respondent->race,
            'region'      => $this->respondent->region,
        ];

        $this->respondentRepository->method('find')
            ->willReturn($this->respondent);

        $actual = $this->respondentService->updateDetails($data);

        self::assertEquals($this->respondent, $actual);
    }

    public function testUpdateDetails_GenderIntegrity(): void
    {
//        $this->expectExceptionCode(400);
//        $this->expectException(Exception::class);

        $this->respondent->gender = Gender::FEMALE;

        $data = [
            'uuid'        => $this->respondent->uuid,
            'dateOfBirth' => $this->respondent->dateOfBirth,
            'employment'  => $this->respondent->employment,
            'gender'      => Gender::MALE,
            'race'        => $this->respondent->race,
            'email'       => $this->respondent->email,
        ];

        $this->respondentRepository->method('find')
            ->willReturn($this->respondent);

        $actual = $this->respondentService->updateDetails($data);

        self::assertEquals($this->respondent, $actual);
    }

    public function testUpdateDetails_RaceIntegrity(): void
    {
//        $this->expectExceptionCode(400);
//        $this->expectException(Exception::class);

        $this->respondent->race = Race::BLACK;

        $data = [
            'uuid'        => $this->respondent->uuid,
            'dateOfBirth' => $this->respondent->dateOfBirth,
            'employment'  => $this->respondent->employment,
            'gender'      => $this->respondent->gender,
            'race'        => Race::WHITE,
            'email'       => $this->respondent->email,
        ];

        $this->respondentRepository->method('find')
            ->willReturn($this->respondent);

        $actual = $this->respondentService->updateDetails($data);

        self::assertEquals($this->respondent, $actual);
    }

    public function testUploadPhoto(): void
    {
        $data = [
            'image' => $this->respondent->profileImage,
        ];

        $this->respondentRepository->method('find')
            ->willReturn($this->respondent);

        $actual = $this->respondentService->uploadPhoto($this->data['uuid'], $data);

        self::assertEquals($this->respondent, $actual);
    }

    public function testRemovePhoto(): void
    {
        $this->respondent->profileImage = ['imageId' => $this->respondent->uuid];

        $this->respondentRepository->method('find')
            ->willReturn($this->respondent);

        $actual = $this->respondentService->removePhoto($this->data['uuid']);

        self::assertEquals($this->respondent, $actual);
    }

    public function testRefreshAccessToken_invalidToken(): void
    {
        $this->expectExceptionCode(401);
        $this->expectException(Exception::class);

        $data = [
            'expiredToken' => 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiIsImtpZCI6ImVtcHR5In0.eyJpc3MiOiJzdXJ2ZXk1NCIsImlhdCI6IjE1ODY0MzYyMTcuNzEyMzM0Iiwic3ViIjoieHgueHgueHh4IiwianRpIjoiZW1wdHkiLCJleHAiOjE1ODY0MzgwMTcsInVzciI6InRlc3RAdGVzdC5jb20iLCJ0ZW4iOiJTdXJ2ZXk1NCIsImF1dCI6Ikg0c0lBQUFBQUFBQ0E2dFdLa3BOTEZDeXFnYlNLYW01QlNXWitYa2dubll5VUx3a0ZjVFVLaTFPTGZKTVViS0tWbEtKTHNuUFRzMnpLaTVOaWxXS3JhM1ZBZW9xTHNqUFMwbk5LeWtHYXdQcVNnRnJ5c1NtUVVkSnU3UWdCV1l1TGlVcHFUbXArSlRBclMxT1JiTVVwMHQxaVBaUWNXbFJXV29sc2VZQ0FRRGZodWZlUWdFQUFBPT0iLCJzcmMiOiJXZWIifQ.Eb-vv4S26J6inaV4DTIRdoYjVVF6Hvl_ZBLnZzBot2k',
            'refreshToken' => '',
        ];

        $this->respondentService->refreshAccessToken($data);
    }

    public function testRefreshAccessToken_expired(): void
    {
        $this->expectExceptionCode(401);
        $this->expectException(Exception::class);

        $data = [
            'expiredToken' => 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiIsImtpZCI6ImVtcHR5In0.eyJpc3MiOiJzdXJ2ZXk1NCIsImlhdCI6IjE1ODY0MzYyMTcuNzEyMzM0Iiwic3ViIjoieHgueHgueHh4IiwianRpIjoiZW1wdHkiLCJleHAiOjE1ODY0MzgwMTcsInVzciI6InRlc3RAdGVzdC5jb20iLCJ0ZW4iOiJTdXJ2ZXk1NCIsImF1dCI6Ikg0c0lBQUFBQUFBQ0E2dFdLa3BOTEZDeXFnYlNLYW01QlNXWitYa2dubll5VUx3a0ZjVFVLaTFPTGZKTVViS0tWbEtKTHNuUFRzMnpLaTVOaWxXS3JhM1ZBZW9xTHNqUFMwbk5LeWtHYXdQcVNnRnJ5c1NtUVVkSnU3UWdCV1l1TGlVcHFUbXArSlRBclMxT1JiTVVwMHQxaVBaUWNXbFJXV29sc2VZQ0FRRGZodWZlUWdFQUFBPT0iLCJzcmMiOiJXZWIifQ.Eb-vv4S26J6inaV4DTIRdoYjVVF6Hvl_ZBLnZzBot2k',
            'refreshToken' => $this->respondent->refreshToken,
        ];

        $this->respondent->refreshTokenExpiry = date('Y-m-d\TH:i:sP', strtotime("yesterday"));

        $this->respondentRepository->method('find')
            ->willReturn($this->respondent);

        $this->respondentService->refreshAccessToken($data);
    }

    public function testRefreshAccessToken(): void
    {
        $data = [
            'expiredToken' => 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiIsImtpZCI6ImVtcHR5In0.eyJpc3MiOiJzdXJ2ZXk1NCIsImlhdCI6IjE1ODY0MzYyMTcuNzEyMzM0Iiwic3ViIjoieHgueHgueHh4IiwianRpIjoiZW1wdHkiLCJleHAiOjE1ODY0MzgwMTcsInVzciI6InRlc3RAdGVzdC5jb20iLCJ0ZW4iOiJTdXJ2ZXk1NCIsImF1dCI6Ikg0c0lBQUFBQUFBQ0E2dFdLa3BOTEZDeXFnYlNLYW01QlNXWitYa2dubll5VUx3a0ZjVFVLaTFPTGZKTVViS0tWbEtKTHNuUFRzMnpLaTVOaWxXS3JhM1ZBZW9xTHNqUFMwbk5LeWtHYXdQcVNnRnJ5c1NtUVVkSnU3UWdCV1l1TGlVcHFUbXArSlRBclMxT1JiTVVwMHQxaVBaUWNXbFJXV29sc2VZQ0FRRGZodWZlUWdFQUFBPT0iLCJzcmMiOiJXZWIifQ.Eb-vv4S26J6inaV4DTIRdoYjVVF6Hvl_ZBLnZzBot2k',
            'refreshToken' => $this->respondent->refreshToken,
        ];

        $this->respondentRepository->method('find')
            ->willReturn($this->respondent);

        $actual = $this->respondentService->refreshAccessToken($data);

        self::assertArrayHasKey('accessToken', $actual);
    }

    public function testDelete(): void
    {
        $this->respondentRepository->method('find')
            ->willReturn($this->respondent);

        $actual = $this->respondentService->delete($this->data['uuid']);

        self::assertArrayHasKey('message', $actual);
    }

    public function testGetTotalEarnings(): void
    {
        $this->respondentRepository->method('find')
            ->willReturn($this->respondent);

        $this->respondentSurveyRepository->method('getCompletedSurveysForRespondent')
            ->willReturn([$this->respondentSurvey]);

        $this->surveyRepository->method('list')
            ->willReturn([(array)$this->survey]);

        $actual = $this->respondentService->getTotalEarnings($this->respondent->uuid);

        $expected = [
            'totalEarnings' => $this->survey->incentive,
            'currency'      => 'ZAR',
        ];

        self::assertEquals($expected, $actual);
    }

    public function testGetSurveyHistory_SurveyNotFound(): void
    {
        $actual = $this->respondentService->getSurveyHistory($this->respondent->uuid);

        self::assertEquals([], $actual);
    }

    public function testGetSurveyHistory(): void
    {
        $this->respondentSurveyRepository->method('getCompletedSurveysForRespondent')
            ->willReturn([$this->respondentSurvey]);

        $survey              = (array)$this->survey;
        $survey['tagLabels'] = '["Food & Drinks","Health"]';

        $this->surveyRepository->method('list')
            ->willReturn([$survey]);

        $actual = $this->respondentService->getSurveyHistory($this->respondent->uuid);

        self::assertEquals([(array)$this->survey], $actual);
    }

    public function testSummaryUpdate(): void
    {
        $this->respondentRepository->method('find')
            ->willReturn($this->respondent);

        $this->openService->method('getAllLsmRecord')
            ->willReturn(Helper::decodeJsonFile(LSM_RECORD_JSON));

        $actual   = $this->respondentService->addLsm($this->respondent->uuid, ['i1', 'i2', 'i3']);
        $expected = [
            'value'   => -0.8580769999999999,
            'summary' => 'LSM 4',
        ];
        self::assertEquals($expected, $actual);
    }
}
