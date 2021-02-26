<?php

namespace Survey54\Reap\Domain;

use Survey54\Library\Domain\Domain;
use Survey54\Library\Domain\Values\AuthStatus;
use Survey54\Library\Domain\Values\SignedUpSource;
use Survey54\Library\Domain\Values\UserStatus;

class Respondent extends Domain
{
    public string $mobile;
    public ?string $email = null;

    public ?string $dateOfBirth = null;
    public ?string $ageGroup = null;
    public ?string $gender = null;
    public ?string $employment = null;
    public ?string $race = null;
    public ?string $lsmGroup = null;
    public ?array $lsm = null;
    public bool $demographicCompleted = false;

    public string $userStatus = UserStatus::DEACTIVATED;
    public string $authStatus = AuthStatus::VERIFIED;
    public ?string $password = null;
    public ?string $action = null;
    public ?string $verificationCode = null;
    public ?string $verificationType = null;
    public ?string $verificationExpiry = null;
    public int $verificationRetries = 0;
    public ?string $refreshToken = null;
    public ?string $refreshTokenExpiry = null;
    public int $loginAttempts = 0;

    public ?string $firstName = null;
    public ?string $lastName = null;
    public ?string $country = null;
    public ?string $region = null;
    public ?string $ipAddress = null;
    public ?array $profileImage = null;
    public string $signedUpSource = SignedUpSource::WEB;
    public bool $convertedFromOpenSurvey = false;
    public bool $markedForDeletion = false;
    public bool $isSample = false;
    public bool $isGhost = false;
    public ?string $ghostMobile = null;
    public bool $promptReview = false;

    /**
     * Respondent constructor.
     * @param string $uuid
     * @param string $mobile
     * @param string|null $createdAt
     * @param string|null $updatedAt
     */
    public function __construct(
        string $uuid,
        string $mobile,
        ?string $createdAt = null,
        ?string $updatedAt = null
    ) {
        parent::__construct($uuid, $createdAt, $updatedAt);
        $this->mobile = $mobile;
    }

    public static function build(array $data, bool $buildByAlias = false): self
    {
        if ($buildByAlias) {
            $data = self::buildByAlias('respondent_', $data);
        }
        $respondent = new self(
            $data['uuid'],
            $data['mobile'],
            $data['createdAt'] ?? null,
            $data['updatedAt'] ?? null
        );

        $respondent->email                   = $data['email'] ?? null;
        $respondent->dateOfBirth             = $data['dateOfBirth'] ?? null;
        $respondent->ageGroup                = $data['ageGroup'] ?? null;
        $respondent->gender                  = $data['gender'] ?? null;
        $respondent->employment              = $data['employment'] ?? null;
        $respondent->country                 = $data['country'] ?? null;
        $respondent->region                  = $data['region'] ?? null;
        $respondent->ipAddress               = $data['ipAddress'] ?? null;
        $respondent->demographicCompleted    = $data['demographicCompleted'] ?? false;
        $respondent->signedUpSource          = $data['signedUpSource'] ?? SignedUpSource::WEB;
        $respondent->convertedFromOpenSurvey = $data['convertedFromOpenSurvey'] ?? false;
        $respondent->profileImage            = $data['profileImage'] ?? null;
        $respondent->userStatus              = $data['userStatus'] ?? UserStatus::DEACTIVATED;
        $respondent->authStatus              = $data['authStatus'] ?? AuthStatus::VERIFIED;
        $respondent->password                = $data['password'] ?? null;
        $respondent->action                  = $data['action'] ?? null;
        $respondent->verificationCode        = $data['verificationCode'] ?? null;
        $respondent->verificationType        = $data['verificationType'] ?? null;
        $respondent->verificationExpiry      = $data['verificationExpiry'] ?? null;
        $respondent->verificationRetries     = $data['verificationRetries'] ?? 0;
        $respondent->refreshToken            = $data['refreshToken'] ?? null;
        $respondent->refreshTokenExpiry      = $data['refreshTokenExpiry'] ?? null;
        $respondent->loginAttempts           = $data['loginAttempts'] ?? 0;
        $respondent->firstName               = $data['firstName'] ?? null;
        $respondent->lastName                = $data['lastName'] ?? null;
        $respondent->race                    = $data['race'] ?? null;
        $respondent->lsm                     = $data['lsm'] ?? null;
        $respondent->lsmGroup                = $data['lsmGroup'] ?? null;
        $respondent->markedForDeletion       = $data['markedForDeletion'] ?? false;
        $respondent->isSample                = $data['isSample'] ?? false;
        $respondent->isGhost                 = $data['isGhost'] ?? false;
        $respondent->ghostMobile             = $data['ghostMobile'] ?? null;
        $respondent->promptReview            = $data['promptReview'] ?? false;

        return $respondent;
    }

    public function jsonSerialize()
    {
        return [
            'uuid'                    => $this->uuid,
            'mobile'                  => $this->mobile,
            'email'                   => $this->email,
            'dateOfBirth'             => $this->dateOfBirth,
            'ageGroup'                => $this->ageGroup,
            'gender'                  => $this->gender,
            'employment'              => $this->employment,
            'country'                 => $this->country,
            'region'                  => $this->region,
            'ipAddress'               => $this->ipAddress,
            'lsm'                     => $this->lsm,
            'lsmGroup'                => $this->lsmGroup,
            'profileImage'            => $this->profileImage,
            'userStatus'              => $this->userStatus ?? UserStatus::DEACTIVATED,
            'authStatus'              => $this->authStatus ?? AuthStatus::VERIFIED,
            'password'                => $this->password,
            'action'                  => $this->action,
            'verificationCode'        => $this->verificationCode,
            'verificationType'        => $this->verificationType,
            'verificationExpiry'      => $this->verificationExpiry,
            'verificationRetries'     => $this->verificationRetries,
            'refreshToken'            => $this->refreshToken,
            'refreshTokenExpiry'      => $this->refreshTokenExpiry,
            'loginAttempts'           => $this->loginAttempts,
            'firstName'               => $this->firstName,
            'lastName'                => $this->lastName,
            'race'                    => $this->race,
            'demographicCompleted'    => $this->demographicCompleted,
            'signedUpSource'          => $this->signedUpSource,
            'convertedFromOpenSurvey' => $this->convertedFromOpenSurvey,
            'markedForDeletion'       => $this->markedForDeletion,
            'isSample'                => $this->isSample,
            'isGhost'                 => $this->isGhost,
            'ghostMobile'             => $this->ghostMobile,
            'promptReview'            => $this->promptReview,
            'createdAt'               => $this->createdAt,
            'updatedAt'               => $this->updatedAt,
        ];
    }
}
