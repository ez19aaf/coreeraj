<?php

namespace Survey54\Reap\Application;

use Survey54\Library\Adapter\CloudinaryAdapter;
use Survey54\Library\Adapter\IpToCountryAdapter;
use Survey54\Library\Domain\Values\AgeGroup;
use Survey54\Library\Domain\Values\AuthAction;
use Survey54\Library\Domain\Values\AuthStatus;
use Survey54\Library\Domain\Values\Country;
use Survey54\Library\Domain\Values\Employment;
use Survey54\Library\Domain\Values\GdprAction;
use Survey54\Library\Domain\Values\Gender;
use Survey54\Library\Domain\Values\LsmGroup;
use Survey54\Library\Domain\Values\Race;
use Survey54\Library\Domain\Values\SignedUpSource;
use Survey54\Library\Domain\Values\UserStatus;
use Survey54\Library\Domain\Values\UserType;
use Survey54\Library\Domain\Values\VerificationType;
use Survey54\Library\Helper\AuthHelper;
use Survey54\Library\Message\TextMessageService;
use Survey54\Library\Token\Token;
use Survey54\Library\Utilities\DateTime;
use Survey54\Library\Utilities\UUID;
use Survey54\Reap\Application\Helper\DobHelper;
use Survey54\Reap\Application\Helper\SegmentationHelper;
use Survey54\Reap\Application\Repository\GdprRepository;
use Survey54\Reap\Application\Repository\GhostRepository;
use Survey54\Reap\Application\Repository\LogRepository;
use Survey54\Reap\Application\Repository\RespondentRepository;
use Survey54\Reap\Application\Repository\RespondentSurveyRepository;
use Survey54\Reap\Application\Repository\SurveyRepository;
use Survey54\Reap\Domain\Gdpr;
use Survey54\Reap\Domain\Log;
use Survey54\Reap\Domain\Respondent;
use Survey54\Reap\Framework\Exception\Error;

class RespondentService
{
    use AuthHelper;

    private GhostRepository $ghostRepository;
    private LogRepository $logRepository;
    private RespondentRepository $respondentRepository;
    private RespondentSurveyRepository $respondentSurveyRepository;
    private SurveyRepository $surveyRepository;
    private GdprRepository $gdprRepository;
    private AppReviewService $appReviewService;
    private GdprService $gdprService;
    private OpenService $openService;
    private TextMessageService $textMessageService;
    private CloudinaryAdapter $imageAdapter;
    private IpToCountryAdapter $ipToCountryAdapter;
    private string $secret;

    /**
     * RespondentService constructor.
     * @param GhostRepository $ghostRepository
     * @param LogRepository $logRepository
     * @param RespondentRepository $respondentRepository
     * @param RespondentSurveyRepository $respondentSurveyRepository
     * @param SurveyRepository $surveyRepository
     * @param GdprRepository $gdprRepository
     * @param AppReviewService $appReviewService
     * @param GdprService $gdprService
     * @param OpenService $openService
     * @param TextMessageService $textMessageService
     * @param CloudinaryAdapter $imageAdapter
     * @param IpToCountryAdapter $ipToCountryAdapter
     * @param string $secret
     */
    public function __construct(
        GhostRepository $ghostRepository,
        LogRepository $logRepository,
        RespondentRepository $respondentRepository,
        RespondentSurveyRepository $respondentSurveyRepository,
        SurveyRepository $surveyRepository,
        GdprRepository $gdprRepository,
        AppReviewService $appReviewService,
        GdprService $gdprService,
        OpenService $openService,
        TextMessageService $textMessageService,
        CloudinaryAdapter $imageAdapter,
        IpToCountryAdapter $ipToCountryAdapter,
        string $secret
    ) {
        $this->ghostRepository            = $ghostRepository;
        $this->logRepository              = $logRepository;
        $this->respondentRepository       = $respondentRepository;
        $this->respondentSurveyRepository = $respondentSurveyRepository;
        $this->surveyRepository           = $surveyRepository;
        $this->gdprRepository             = $gdprRepository;
        $this->appReviewService           = $appReviewService;
        $this->gdprService                = $gdprService;
        $this->openService                = $openService;
        $this->textMessageService         = $textMessageService;
        $this->imageAdapter               = $imageAdapter;
        $this->ipToCountryAdapter         = $ipToCountryAdapter;
        $this->secret                     = $secret;
    }

    protected function getSecret(): string
    {
        return $this->secret;
    }

    /**
     * @param string $uuid
     * @param bool $expectActivated
     * @return Respondent
     */
    public function find(string $uuid, bool $expectActivated = true): Respondent
    {
        $user           = $this->userMustExistAndBeActivated($uuid, $expectActivated);
        $user->password = null; // hide password
        return $user;
    }

    /**
     * @param array $data
     * @return array
     */
    public function sendText(array $data): array
    {
        $search = [
            'country' => ['EQUALS', $data['country']],
            'isGhost' => ['EQUALS', 0],
        ];
        $mList  = $this->respondentRepository->list(0, $data['limit'], $search, ['+userStatus'], 'mobile'); // Sorting by +userStatus will list ACTIVATED first
        $count  = count($mList);
        if ($count === 0) {
            return [
                'message' => 'No respondent found in search',
            ];
        }

        $numbers = array_column($mList, 'mobile');

        // Send text in 1000 batch
        $batch    = 1000;
        $dateAsId = date('d-m-Y') . '-' . str_replace(' ', '-', strtolower($data['country'])); // 14-09-2020-south-africa

        if ($count <= $batch) {
            // Send in batch of $rCount if count is <= $batch
            $request  = [
                'count'   => $count,
                'numbers' => $numbers,
            ];
            $response = $this->textMessageService->sendText($numbers, $data['text']);
            $log      = new Log(UUID::generate(), $dateAsId, 'Date', 'SendText', $request, $response);
            $this->logRepository->add($log);
        } else {
            // Send in batches of $batch if count is above $batch
            $chunks = array_chunk($numbers, 1000);
            foreach ($chunks as $chunk) {
                $request  = [
                    'count'   => count($chunk),
                    'numbers' => $chunk,
                ];
                $response = $this->textMessageService->sendText($chunk, $data['text']);
                $log      = new Log(UUID::generate(), $dateAsId, 'Date', 'SendText', $request, $response);
                $this->logRepository->add($log);
            }
        }

        return [
            'message' => 'Text sent successfully',
        ];
    }

    /**
     * @param array $data
     * @return array
     */
    public function create(array $data): array
    {
        $updateUser = false;

        /** @var $respondent Respondent */
        if (isset($data['mobile']) && $respondent = $this->respondentRepository->findByMobile($data['mobile'])) {
            if ($respondent->userStatus === UserStatus::ACTIVATED) {
                Error::throwError(Error::S542_USER_ALREADY_REGISTERED_MOBILE);
            } else {
                /** @var Gdpr $gdpr */
                $gdpr = $this->gdprRepository->findByUserId($respondent->uuid);

                if ($gdpr && $gdpr->action === GdprAction::DELETE_ACCOUNT) {
                    $this->gdprRepository->delete($gdpr->uuid);
                }
                $updateUser = true;
            }
        }

        if (isset($data['email']) && ($r = $this->respondentRepository->findByEmail($data['email'])) &&
            !$updateUser | ($updateUser && $r->email !== $data['email'])) {
            Error::throwError(Error::S542_USER_ALREADY_REGISTERED_EMAIL);
        }

        $data['userStatus']         = UserStatus::ACTIVATED;
        $data['authStatus']         = AuthStatus::AWAITING_VERIFICATION;
        $data['password']           = password_hash($data['password'], PASSWORD_DEFAULT);
        $data['action']             = AuthAction::REGISTER;
        $data['verificationCode']   = $this->randomString(6, true);
        $data['verificationExpiry'] = $this->generateExpiration('PT10M'); //10 Minutes
        $data['verificationType']   = VerificationType::MOBILE;
        $data['loginAttempts']      = 0;
        $data['signedUpSource']     = $data['signedUpSource'] ?? SignedUpSource::WEB;
        $data['markedForDeletion']  = false; // explicit set for $updateUser

        if (isset($data['dateOfBirth'])) {
            $data['ageGroup'] = DobHelper::getAgeGroupFromDOB($data['dateOfBirth']);
        }

        $ghost = $this->ghostRepository->findByMobile($data['mobile']);

        if (!$ghost) {
            $this->ipToCountryAdapter->validate($data['ipAddress'], $data['country']);
        }

        $data = SegmentationHelper::demographicCompletedCheck($data);

        if ($updateUser) {
            $data['uuid'] = $respondent->uuid;
            // Check Ghost
            $mobile = $ghost->ghostMobile ?? $respondent->mobile;
            $this->textMessageService->sendRegistrationOTP($mobile, $data['verificationCode']);
            $this->updateDetails($data, false);
        } else {
            $data['uuid'] = UUID::generate();
            $respondent   = Respondent::build($data);
            // Check Ghost
            if ($ghost) {
                $respondent->ghostMobile = $ghost->ghostMobile;
                $respondent->isGhost     = true;
                $this->textMessageService->sendRegistrationOTP($ghost->ghostMobile, $data['verificationCode']);
            } else {
                $this->textMessageService->sendRegistrationOTP($respondent->mobile, $data['verificationCode']);
            }
            $this->respondentRepository->add($respondent);
        }

        return [
            'message' => 'You have been successfully registered. Please use the code sent to your mobile to complete the sign up process.',
        ];
    }

    /**
     * @param array $data
     * @return array
     */
    public function verify(array $data): array
    {
        $user = $this->userMustExistByMobileAndBeActivated($data['value']);
        // check that code is in db
        if ($user->verificationCode !== $data['verificationCode']) {
            Error::throwError(Error::S54_VERIFICATION_CODE_INVALID);
        }
        // invalid verification type
        if ($user->verificationType !== VerificationType::MOBILE) {
            Error::throwError(Error::S54_INVALID_VERIFICATION_TYPE_MOBILE);
        }
        // awaiting verification required
        if ($user->authStatus !== AuthStatus::AWAITING_VERIFICATION) {
            Error::throwError(Error::S54_AUTH_AWAITING_VERIFICATION_REQUIRED);
        }
        // check that code is not expired
        if (DateTime::generate() > $user->verificationExpiry) {
            Error::throwError(Error::S54_VERIFICATION_CODE_EXPIRED);
        }

        // update authStatus to await action
        /** @var Respondent $user */
        $user             = $this->respondentRepository->find($user->uuid);
        $user->authStatus = AuthStatus::AWAITING_ACTION;
        $this->respondentRepository->update($user);

        // login after REGISTER action verification
        if ($user->action === AuthAction::REGISTER) {
            // Ensure that refreshToken is unique in DB
            do {
                $refreshToken       = $this->generateRefreshToken();
                $search             = [
                    'refreshToken' => ['EQUALS', $refreshToken],
                ];
                $refreshTokenExists = $this->respondentRepository->findBy($search) ? true : false;
            } while ($refreshTokenExists);

            // Reset loginAttempts to 0. Save refreshToken.
            /** @var Respondent $user */
            $user                     = $this->respondentRepository->find($user->uuid);
            $user->verificationCode   = ''; // empty this, to allow more code uniqueness for mobile
            $user->verificationExpiry = '';
            $user->action             = AuthAction::COMPLETE;
            $user->authStatus         = AuthStatus::VERIFIED;
            $user->loginAttempts      = 0;
            $user->refreshToken       = $refreshToken;
            $user->refreshTokenExpiry = $this->generateExpiration('P3M');
            $this->respondentRepository->update($user);

            // Call app review logic
            $this->appReviewService->promptReview($user->uuid);

            $email       = $user->email ?? "{$user->uuid}@survey54.com";
            $accessToken = $this->generateAccessToken(UserType::RESPONDENT, $user->uuid, $email);

            return [
                'userId'       => $user->uuid,
                'accessToken'  => $accessToken,  // 30 minutes
                'refreshToken' => $refreshToken, // 3 months
            ];
        }

        return [
            'userId' => $user->uuid,
        ];
    }

    /**
     * @param string $uuid
     * @param string $password
     * @return array
     */
    public function setPasswordAndLogin(string $uuid, string $password): array
    {
        $user = $this->userMustExistAndBeActivated($uuid);
        if ($user->authStatus !== AuthStatus::AWAITING_ACTION) {
            Error::throwError(Error::S54_AUTH_AWAITING_ACTION_REQUIRED);
        }

        /** @var Respondent $user */
        $user                     = $this->respondentRepository->find($user->uuid);
        $user->verificationCode   = ''; // empty this, to allow more code uniqueness for mobile
        $user->verificationExpiry = '';
        $user->password           = $this->hashPassword($password);
        $user->action             = AuthAction::COMPLETE;
        $user->authStatus         = AuthStatus::VERIFIED;
        $user->loginAttempts      = 0;
        $this->respondentRepository->update($user);

        $login = [
            'type'     => isset($user->mobile) ? VerificationType::MOBILE : VerificationType::EMAIL,
            'value'    => $user->mobile ?? $user->email,
            'password' => $password,
        ];

        return $this->login($login);
    }

    /**
     * @param array $data
     * @return array
     */
    public function login(array $data): array
    {
        $user = $this->userMustExistByMobileAndBeActivated($data['value']);

        if ($user->loginAttempts >= 5) {
            Error::throwError(Error::S54_LOGIN_ATTEMPTS_ERROR);
        }

        // Account must be Verified
        if ($user->authStatus !== AuthStatus::VERIFIED) {
            if ($user->action !== AuthAction::FORGOT_PASSWORD) {
                Error::throwError(Error::S54_ACCOUNT_NOT_FULLY_VERIFIED);
            }

            // Allow FORGOT_PASSWORD action if the users successfully attempts login
            /** @var Respondent $user */
            $user             = $this->respondentRepository->find($user->uuid);
            $user->authStatus = AuthStatus::VERIFIED;
            $user->action     = AuthAction::COMPLETE;
            $this->respondentRepository->update($user);
        }

        if (!password_verify($data['password'], $user->password)) {
            /** @var Respondent $user */
            $user = $this->respondentRepository->find($user->uuid);
            ++$user->loginAttempts;
            $this->respondentRepository->update($user);

            Error::throwError(Error::S54_PASSWORD_MISMATCH);
        }

        do { // Ensure that refreshToken is unique in DB
            $refreshToken       = $this->generateRefreshToken();
            $search             = [
                'refreshToken' => ['EQUALS', $refreshToken],
            ];
            $refreshTokenExists = $this->respondentRepository->findBy($search) ? true : false;
        } while ($refreshTokenExists);

        // Reset loginAttempts to 0. Save refreshToken. Reset verificationRetries to 0;
        /** @var Respondent $user */
        $user                      = $this->respondentRepository->find($user->uuid);
        $user->loginAttempts       = 0;
        $user->verificationRetries = 0;
        $user->refreshToken        = $refreshToken;
        $user->refreshTokenExpiry  = $this->generateExpiration('P3M');
        $this->respondentRepository->update($user);

        // Demographic check
        $data = SegmentationHelper::demographicCompletedCheck($user->toArray());
        $this->respondentRepository->update(Respondent::build($data));

        // Call app review logic
        $this->appReviewService->promptReview($user->uuid);

        $email = $user->email ?? "{$user->uuid}@survey54.com";

        $accessToken = $this->generateAccessToken(UserType::RESPONDENT, $user->uuid, $email);

        $result                 = $this->find($user->uuid)->toArray();
        $result['accessToken']  = $accessToken; // 30 minutes
        $result['refreshToken'] = $refreshToken; // 3 months

        return $result;
    }

    /**
     * @param array $data
     */
    public function forgotPassword(array $data): void
    {
        $user = $this->userMustExistByMobileAndBeActivated($data['value']);

        /** @var Respondent $user */
        $user                     = $this->respondentRepository->find($user->uuid);
        $user->authStatus         = AuthStatus::AWAITING_VERIFICATION;
        $user->verificationCode   = $this->randomString(6, true);
        $user->verificationExpiry = $this->generateExpiration('PT120M'); //120 Minutes
        $user->verificationType   = VerificationType::MOBILE;
        $user->action             = AuthAction::FORGOT_PASSWORD;
        $this->respondentRepository->update($user);

        // Check Ghost
        $mobile = ($ghost = $this->ghostRepository->findByMobile($user->mobile)) ? $ghost->ghostMobile : $user->mobile;

        $this->textMessageService->sendForgotPasswordOTP($mobile, $user->verificationCode);
    }

    /**
     * @param string $uuid
     * @param string $status
     * @return Respondent
     */
    public function changeStatus(string $uuid, string $status): Respondent
    {
        $this->userMustExistAndBeActivated($uuid, false); // just user must exist

        /** @var Respondent $user */
        $user             = $this->respondentRepository->find($uuid);
        $user->userStatus = $status;
        $this->respondentRepository->update($user);

        return $this->find($uuid, false);
    }

    /**
     * @param array $data
     * @return Respondent
     */
    public function changePassword(array $data): Respondent
    {
        $user = $this->userMustExistAndBeActivated($data['uuid']);

        //passwordMustMatch
        if (!password_verify($data['oldPassword'], $user->password)) {
            /** @var Respondent $user */
            $user = $this->respondentRepository->find($user->uuid);
            ++$user->loginAttempts;
            $this->respondentRepository->update($user);

            Error::throwError(Error::S54_PASSWORD_MISMATCH);
        }

        if (strcmp($data['password'], $data['oldPassword']) === 0) {
            Error::throwError(Error::S542_SAME_PASSWORD_ERROR);
        }

        // Verified auth is required
        if ($user->authStatus !== AuthStatus::VERIFIED) {
            Error::throwError(Error::S54_AUTH_VERIFIED_REQUIRED);
        }

        /** @var Respondent $user */
        $user           = $this->respondentRepository->find($user->uuid);
        $user->password = $this->hashPassword($data['password']);
        $this->respondentRepository->update($user);

        return $this->find($user->uuid);
    }

    /**
     * @param array $data
     * @return array
     */
    public function sendNewVerificationCode(array $data): array
    {
        $user = $this->userMustExistByMobileAndBeActivated($data['value']);
        if ($user->verificationRetries >= 4) {
            Error::throwError(Error::S54_VERIFICATION_RETRY_LIMIT);
        }
        // invalid verification type
        if ($user->verificationType !== VerificationType::MOBILE) {
            Error::throwError(Error::S54_INVALID_VERIFICATION_TYPE_MOBILE);
        }
        // awaiting verification required
        if ($user->authStatus !== AuthStatus::AWAITING_VERIFICATION) {
            Error::throwError(Error::S54_AUTH_AWAITING_VERIFICATION_REQUIRED);
        }
        // flow cases where send new verification code are allowed
        if ($user->action !== AuthAction::REGISTER && $user->action !== AuthAction::FORGOT_PASSWORD) {
            Error::throwError(Error::S54_INVALID_VERIFICATION_FLOW);
        }

        /** @var Respondent $user */
        $user                     = $this->respondentRepository->find($user->uuid);
        $user->verificationType   = VerificationType::MOBILE;
        $user->verificationCode   = $this->randomString(6, true);
        $user->verificationExpiry = $this->generateExpiration('PT120M'); //120 Minutes
        ++$user->verificationRetries;
        $this->respondentRepository->update($user);

        // Check Ghost
        $mobile = ($ghost = $this->ghostRepository->findByMobile($user->mobile)) ? $ghost->ghostMobile : $user->mobile;

        if ($user->action === AuthAction::REGISTER) {
            $this->textMessageService->sendRegistrationOTP($mobile, $user->verificationCode);
        } else {
            $this->textMessageService->sendForgotPasswordOTP($mobile, $user->verificationCode);
        }

        return [
            'flowType' => $user->action,
        ];
    }

    /**
     * @param array $data
     * @param bool $expectActivated
     * @return Respondent
     */
    public function updateDetails(array $data, bool $expectActivated = true): Respondent
    {
        if (isset($data['mobile']) && ($user = $this->respondentRepository->findByMobile($data['mobile'])) && $user->uuid !== $data['uuid']) {
            Error::throwError(Error::S542_USER_ALREADY_REGISTERED_MOBILE);
        }
        if (isset($data['email']) && ($user = $this->respondentRepository->findByEmail($data['email'])) && $user->uuid !== $data['uuid']) {
            Error::throwError(Error::S542_USER_ALREADY_REGISTERED_EMAIL);
        }

        $user = $this->userMustExistAndBeActivated($data['uuid'], $expectActivated);

        if ($expectActivated) {
            if ($user->dateOfBirth !== $data['dateOfBirth'] && \DateTime::createFromFormat('d-m-Y', $user->dateOfBirth) !== false) {
                $data['dateOfBirth'] = $user->dateOfBirth; // just reset to old dateOfBirth
                $data['ageGroup']    = DobHelper::getAgeGroupFromDOB($data['dateOfBirth']);
            }
            if ($user->gender !== $data['gender'] && in_array($user->gender, Gender::toArray(), true)) {
                $data['gender'] = $user->gender; // just reset to old gender
            }
            if ($user->race !== $data['race'] && in_array($user->race, Race::toArray(), true)) {
                $data['race'] = $user->race; // just reset to old race
            }
            if ($user->email !== null && $data['email'] !== null && $user->email !== $data['email']) {
                Error::throwError(Error::S54_EMAIL_CHANGE);
            }
        }

        $data = array_merge($user->toArray(), $data);
        $data = SegmentationHelper::demographicCompletedCheck($data);

        $this->respondentRepository->update(Respondent::build($data));

        return $this->find($user->uuid);
    }

    /**
     * @param string $userId
     * @param array $data
     * @return Respondent
     */
    public function uploadPhoto(string $userId, array $data): Respondent
    {
        $this->userMustExistAndBeActivated($userId);

        $imageUrl = $this->imageAdapter->write([
            'imageId'  => "resp/$userId",
            'rawImage' => $data['image'],
        ]);

        /** @var Respondent $user */
        $user               = $this->respondentRepository->find($userId);
        $user->profileImage = [
            'imageId'  => $userId,
            'imageUrl' => $imageUrl,
        ];
        $this->respondentRepository->update($user);

        return $this->find($userId);
    }

    /**
     * @param string $userId
     * @return Respondent
     */
    public function removePhoto(string $userId): Respondent
    {
        $user = $this->userMustExistAndBeActivated($userId);
        if (!empty($user->profileImage)) {
            $imageId = $user->profileImage['imageId'];
            if (!empty($imageId)) {
                $this->imageAdapter->delete($imageId);

                /** @var Respondent $user */
                $user               = $this->respondentRepository->find($userId);
                $user->profileImage = null;
                $this->respondentRepository->update($user);
            }
        }
        return $this->find($userId);
    }

    /**
     * @param array $data
     * @return array
     */
    public function refreshAccessToken(array $data): array
    {
        //Verify old access token, ignoring expiration
        $oldToken = new Token($data['expiredToken'], $this->secret, false);
        $oldToken->verify('hmac', true);

        $email  = $oldToken->need('usr');
        $id     = $oldToken->need('sub');
        $tenant = $oldToken->need('ten');

        /** @var $user Respondent */
        if ($tenant !== 'Survey54' || !($user = $this->respondentRepository->find($id)) || $user->refreshToken !== $data['refreshToken']) {
            Error::throwError(Error::S54_ACCESS_TOKEN_INVALID);
        }

        $currentTime = DateTime::generate();
        if ($currentTime > $user->refreshTokenExpiry) {
            Error::throwError(Error::S54_REFRESH_TOKEN_EXPIRED);
        }

        $accessToken = $this->generateAccessToken(UserType::RESPONDENT, $user->uuid, $email);
        return ['accessToken' => $accessToken];
    }

    /**
     * @param string $userId
     * @return array
     */
    public function delete(string $userId): array
    {
        /** @var Respondent $user */
        $user                    = $this->respondentRepository->find($userId);
        $user->userStatus        = UserStatus::DEACTIVATED;
        $user->markedForDeletion = true;
        $this->respondentRepository->update($user);

        $this->gdprService->scheduleDeletion($userId);

        return [
            'message' => 'Your account has been deactivated and is now set to automatically delete in 30 days. ' .
                'To reactivate your account please contact us at info@survey54.com. After 30 days this account will be permanently deleted.',
        ];
    }

    /**
     * @param string $respondentId
     * @return array
     */
    public function getTotalEarnings(string $respondentId): array
    {
        $respondent    = $this->find($respondentId);
        $totalEarnings = 0;
        $sr            = $this->respondentSurveyRepository->getCompletedSurveysForRespondent($respondentId);
        if ($sr) {
            $surveyIds      = array_column($sr, 'surveyId');
            $search['uuid'] = ['IN', $surveyIds];
            $surveys        = $this->surveyRepository->list(0, 0, $search);
            if ($surveys) {
                foreach ($surveys as $survey) {
                    $totalEarnings += $survey['incentive'];
                }
            }
        }
        switch ($respondent->country) {
            case Country::GHANA:
                $currency = 'GHS';
                break;
            case Country::KENYA:
                $currency = 'KES';
                break;
            case Country::NIGERIA:
                $currency = 'NGN';
                break;
            case Country::SOUTH_AFRICA:
                $currency = 'ZAR';
                break;
            default:
                $currency = 'USD';
        }
        return [
            'totalEarnings' => $totalEarnings,
            'currency'      => $currency,
        ];
    }

    /**
     * @param string $respondentId
     * @return array
     */
    public function getSurveyHistory(string $respondentId): array
    {
        // change later to single join stmt
        $sr = $this->respondentSurveyRepository->getCompletedSurveysForRespondent($respondentId);
        if ($sr) {
            $surveyIds      = array_column($sr, 'surveyId');
            $search['uuid'] = ['IN', $surveyIds];
            $select         = '`uuid`,`title`,`description`,`image`,`tagLabels`,`incentive`,`incentiveCurrency`';
            $surveys        = $this->surveyRepository->list(0, 0, $search, null, $select);
            if ($surveys) {
                foreach ($surveys as &$survey) {
                    $survey['tagLabels'] = json_decode($survey['tagLabels'] ?? json_encode(null, JSON_THROW_ON_ERROR), true, 512, JSON_THROW_ON_ERROR);
                }
                return $surveys;
            }
        }
        return [];
    }

    /**
     * @param array $data
     * @return array
     */
    public function checkMobile(array $data): array
    {
        $mobile               = $data['mobile'];
        $exists               = false;
        $respondentId         = null;
        $demographicCompleted = false;
        $remainingFields      = null;

        if ($respondent = $this->respondentRepository->findByMobile($mobile)) {
            $exists               = true;
            $respondentId         = $respondent->uuid;
            $demographicCompleted = $respondent->demographicCompleted;
            $remainingFields      = SegmentationHelper::demographicCompletedCheck($respondent->toArray())['remainingFields'];
        }

        return [
            'respondentId'         => $respondentId,
            'mobile'               => $mobile,
            'exists'               => $exists,
            'demographicCompleted' => $demographicCompleted,
            'remainingFields'      => $remainingFields, // fields to be provided
        ];
    }

    /**
     * @param string $uuid
     * @param array $lsmKeys
     * @return array
     */
    public function addLsm(string $uuid, array $lsmKeys): array
    {
        if (empty($lsmKeys)) {
            return [];
        }

        $lsmRecord = $this->openService->getAllLsmRecord();

        $initialValue = $lsmRecord['initialValue'];
        foreach ($lsmRecord['options'] as $options) {
            if (in_array($options['id'], $lsmKeys, true)) {
                if ($options['operator'] === '+') {
                    $initialValue += $options['value'];
                } else if ($options['operator'] === '-') {
                    $initialValue -= $options['value'];
                }
            }
        }

        $text = $lsmGroup = '';
        foreach ($lsmRecord['summary'] as $summary) {
            if ($summary['value'] > $initialValue) {
                $text = $summary['text'];
            }
        }

        switch ($text) {
            case 'LSM 1':
            case 'LSM 2':
            case 'LSM 3':
            case 'LSM 4':
                $lsmGroup = LsmGroup::LSM_1_4;
                break;
            case 'LSM 5':
            case 'LSM 6':
                $lsmGroup = LsmGroup::LSM_5_6;
                break;
            case 'LSM 7 Low':
            case 'LSM 7 High':
            case 'LSM 8 Low':
            case 'LSM 8 High':
                $lsmGroup = LsmGroup::LSM_7_8;
                break;
            case 'LSM 9 Low':
            case 'LSM 9 High':
            case 'LSM 10 Low':
            case 'LSM 10 High':
                $lsmGroup = LsmGroup::LSM_9_10;
                break;
        }

        if (empty($lsmGroup)) {
            return [];
        }

        $lsm = [
            'value'   => $initialValue,
            'summary' => $text,
        ];

        /** @var Respondent $user */
        $user           = $this->respondentRepository->find($uuid);
        $user->lsm      = $lsm;
        $user->lsmGroup = $lsmGroup;
        $this->respondentRepository->update($user);

        return $lsm;
    }

    /**
     * @return array
     */
    public function distribution(): array
    {
        $stats = [
            'respondentsCount' => $this->respondentRepository->count(),
            'note'             => 'The distribution stats below are in percentage not count.',
        ];

        $agSearch = function (string $country, string $ageGroup): float {
            $totalSearch = [
                'country'  => ['EQUALS', $country],
                'ageGroup' => ['IN', AgeGroup::toArray()],
            ];
            $total       = $this->respondentRepository->count($totalSearch);

            $itemSearch = [
                'country'  => ['EQUALS', $country],
                'ageGroup' => ['EQUALS', $ageGroup],
            ];
            $item       = $this->respondentRepository->count($itemSearch);

            return round(($item / $total) * 100, 2);
        };
        $emSearch = function (string $country, string $employment): float {
            $totalSearch = [
                'country'    => ['EQUALS', $country],
                'employment' => ['IN', Employment::toArray()],
            ];
            $total       = $this->respondentRepository->count($totalSearch);

            $itemSearch = [
                'country'    => ['EQUALS', $country],
                'employment' => ['EQUALS', $employment],
            ];
            $item       = $this->respondentRepository->count($itemSearch);

            return round(($item / $total) * 100, 2);
        };
        $geSearch = function (string $country, string $gender): float {
            $totalSearch = [
                'country' => ['EQUALS', $country],
                'gender'  => ['IN', Gender::toArray()],
            ];
            $total       = $this->respondentRepository->count($totalSearch);

            $itemSearch = [
                'country' => ['EQUALS', $country],
                'gender'  => ['EQUALS', $gender],
            ];
            $item       = $this->respondentRepository->count($itemSearch);

            return round(($item / $total) * 100, 2);
        };
        $lsSearch = function (string $country, string $lsmGroup): float {
            $totalSearch = [
                'country'  => ['EQUALS', $country],
                'lsmGroup' => ['IN', LsmGroup::toArray()],
            ];
            $total       = $this->respondentRepository->count($totalSearch);

            $itemSearch = [
                'country'  => ['EQUALS', $country],
                'lsmGroup' => ['EQUALS', $lsmGroup],
            ];
            $item       = $this->respondentRepository->count($itemSearch);

            return round(($item / $total) * 100, 2);
        };
        $raSearch = function (string $country, string $race): float {
            $totalSearch = [
                'country' => ['EQUALS', $country],
                'race'    => ['IN', Race::toArray()],
            ];
            $total       = $this->respondentRepository->count($totalSearch);

            $itemSearch = [
                'country' => ['EQUALS', $country],
                'race'    => ['EQUALS', $race],
            ];
            $item       = $this->respondentRepository->count($itemSearch);

            return round(($item / $total) * 100, 2);
        };

        foreach (Country::toArray() as $country) {
            $stats[$country] = [
                'ageGroup'   => [
                    AgeGroup::AGE_16_17   => $agSearch($country, AgeGroup::AGE_16_17),
                    AgeGroup::AGE_18_24   => $agSearch($country, AgeGroup::AGE_18_24),
                    AgeGroup::AGE_25_34   => $agSearch($country, AgeGroup::AGE_25_34),
                    AgeGroup::AGE_35_44   => $agSearch($country, AgeGroup::AGE_35_44),
                    AgeGroup::AGE_45_54   => $agSearch($country, AgeGroup::AGE_45_54),
                    AgeGroup::AGE_55_PLUS => $agSearch($country, AgeGroup::AGE_55_PLUS),
                ],
                'employment' => [
                    Employment::EMPLOYED      => $emSearch($country, Employment::EMPLOYED),
                    Employment::UNEMPLOYED    => $emSearch($country, Employment::UNEMPLOYED),
                    Employment::SELF_EMPLOYED => $emSearch($country, Employment::SELF_EMPLOYED),
                ],
                'gender'     => [
                    Gender::MALE   => $geSearch($country, Gender::MALE),
                    Gender::FEMALE => $geSearch($country, Gender::FEMALE),
                ],
            ];

            if ($country === Country::SOUTH_AFRICA) {
                $stats[$country]['lsmGroup'] = [
                    LsmGroup::LSM_1_4  => $lsSearch($country, LsmGroup::LSM_1_4),
                    LsmGroup::LSM_5_6  => $lsSearch($country, LsmGroup::LSM_5_6),
                    LsmGroup::LSM_7_8  => $lsSearch($country, LsmGroup::LSM_7_8),
                    LsmGroup::LSM_9_10 => $lsSearch($country, LsmGroup::LSM_9_10),
                ];
                $stats[$country]['race']     = [
                    Race::ASIAN    => $raSearch($country, Race::ASIAN),
                    Race::BLACK    => $raSearch($country, Race::BLACK),
                    Race::COLOURED => $raSearch($country, Race::COLOURED),
                    Race::WHITE    => $raSearch($country, Race::WHITE),
                ];
            }
        }

        return $stats;
    }



    ////////// Helpers

    /**
     * @param string $mobile
     * @return Respondent
     */
    public function userMustExistByMobileAndBeActivated(string $mobile): Respondent
    {
        /** @var $user Respondent */
        if (!$user = $this->respondentRepository->findByMobile($mobile)) {
            Error::throwError(Error::S542_RESPONDENT_NOT_FOUND);
        }

        if ($user->userStatus !== UserStatus::ACTIVATED) {
            if ($user->markedForDeletion) {
                Error::throwError(Error::S542_RESPONDENT_NOT_FOUND);
            }
            Error::throwError(Error::S542_RESPONDENT_NOT_ACTIVATED);
        }
        return $user;
    }

    /**
     * @param string $uuid
     * @param bool $expectActivated
     * @return Respondent
     */
    public function userMustExistAndBeActivated(string $uuid, bool $expectActivated = true): Respondent
    {
        /** @var $user Respondent */
        if (!$user = $this->respondentRepository->find($uuid)) {
            Error::throwError(Error::S542_RESPONDENT_NOT_FOUND);
        }
        if ($expectActivated && $user->userStatus !== UserStatus::ACTIVATED) {
            if ($user->markedForDeletion) {
                Error::throwError(Error::S542_RESPONDENT_NOT_FOUND);
            }
            Error::throwError(Error::S542_RESPONDENT_NOT_ACTIVATED);
        }
        return $user;
    }
}
