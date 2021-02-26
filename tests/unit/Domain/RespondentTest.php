<?php

namespace Tests\Unit\Domain;

use Survey54\Reap\Domain\Respondent;
use Tests\Unit\AbstractTestCase;

class RespondentTest extends AbstractTestCase
{
    public function testRespondent(): void
    {
        $data = self::$respondentData;

        $respondent = new Respondent(
            $data['uuid'],
            $data['mobile'],
            null,
            null,
        );

        $respondent->email                   = $data['email'];
        $respondent->dateOfBirth             = $data['dateOfBirth'];
        $respondent->ageGroup                = $data['ageGroup'];
        $respondent->gender                  = $data['gender'];
        $respondent->employment              = $data['employment'];
        $respondent->country                 = $data['country'];
        $respondent->region                  = $data['region'];
        $respondent->ipAddress               = $data['ipAddress'];
        $respondent->demographicCompleted    = $data['demographicCompleted'];
        $respondent->signedUpSource          = $data['signedUpSource'];
        $respondent->convertedFromOpenSurvey = $data['convertedFromOpenSurvey'];
        $respondent->password                = $data['password'];
        $respondent->loginAttempts           = $data['loginAttempts'];
        $respondent->verificationCode        = $data['verificationCode'];
        $respondent->verificationExpiry      = $data['verificationExpiry'];
        $respondent->verificationRetries     = $data['verificationRetries'];
        $respondent->verificationType        = $data['verificationType'];
        $respondent->refreshToken            = $data['refreshToken'];
        $respondent->refreshTokenExpiry      = $data['refreshTokenExpiry'];
        $respondent->firstName               = $data['firstName'];
        $respondent->lastName                = $data['lastName'];
        $respondent->race                    = $data['race'];
        $respondent->lsmGroup                = $data['lsmGroup'];
        $respondent->lsm                     = $data['lsm'];
        $respondent->markedForDeletion       = $data['markedForDeletion'];
        $respondent->isSample                = $data['isSample'];
        $respondent->profileImage            = $data['profileImage'];
        $respondent->action                  = $data['action'];
        $respondent->isGhost                 = $data['isGhost'];
        $respondent->ghostMobile             = $data['ghostMobile'];
        $respondent->promptReview            = $data['promptReview'];

        self::assertEquals($data['uuid'], $respondent->uuid);
        self::assertEquals($data['email'], $respondent->email);
        self::assertEquals($data['mobile'], $respondent->mobile);
        self::assertEquals($data['dateOfBirth'], $respondent->dateOfBirth);
        self::assertEquals($data['ageGroup'], $respondent->ageGroup);
        self::assertEquals($data['gender'], $respondent->gender);
        self::assertEquals($data['employment'], $respondent->employment);
        self::assertEquals($data['country'], $respondent->country);
        self::assertEquals($data['region'], $respondent->region);
        self::assertEquals($data['ipAddress'], $respondent->ipAddress);
        self::assertEquals($data['demographicCompleted'], $respondent->demographicCompleted);
        self::assertEquals($data['signedUpSource'], $respondent->signedUpSource);
        self::assertEquals($data['convertedFromOpenSurvey'], $respondent->convertedFromOpenSurvey);
        self::assertEquals($data['markedForDeletion'], $respondent->markedForDeletion);
        self::assertEquals($data['isSample'], $respondent->isSample);
        self::assertNull($respondent->createdAt);

        $actualData = $respondent->toArray();

        self::assertEquals($data, $actualData);
    }

    public function testBuild(): void
    {
        $data = self::$respondentData;

        $actual = Respondent::build($data, true);

        self::assertEquals($data, $actual->toArray());
    }
}
