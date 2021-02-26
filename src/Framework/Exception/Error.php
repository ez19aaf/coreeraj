<?php

namespace Survey54\Reap\Framework\Exception;

use Survey54\Library\Exception\BaseError;

class Error extends BaseError
{
    public const S542_INVALID_DATE_OF_BIRTH = [
        'code'       => 'S542_INVALID_DATE_OF_BIRTH',
        'message'    => 'The date of birth entered must not be in the future or less than 1 years ago.',
        'statusCode' => 400,
    ];
    public const S542_UNDER_AGE             = [
        'code'       => 'S542_UNDER_AGE',
        'message'    => 'You must be at least 16 years old to create an account.',
        'statusCode' => 400,
    ];
    public const S542_SURVEY_RETRIEVE_ERROR = [
        'code'       => 'S542_SURVEY_RETRIEVE_ERROR',
        'message'    => 'There was an error fetching the surveys from Cint',
        'statusCode' => 400,
    ];

    public const S54_RESPONSE_MUST_BE_NUMBER     = [
        'code'       => 'S54_RESPONSE_MUST_BE_NUMBER',
        'message'    => 'Response must be a number.',
        'statusCode' => 400,
    ];
    public const S54_RESPONSE_MUST_BE_IN_OPTIONS = [
        'code'       => 'S54_RESPONSE_MUST_BE_IN_OPTIONS',
        'message'    => 'Response must be in options.',
        'statusCode' => 400,
    ];
    public const S54_RACE_DOES_NOT_APPLY         = [
        'code'       => 'S54_RACE_DOES_NOT_APPLY',
        'message'    => 'Race does not apply to this survey.',
        'statusCode' => 400,
    ];
    public const S54_LSM_GROUP_DOES_NOT_APPLY    = [
        'code'       => 'S54_LSM_GROUP_DOES_NOT_APPLY',
        'message'    => 'LSM Group does not apply to this survey.',
        'statusCode' => 400,
    ];

    public const S54_QUESTIONS_NOT_FOUND = [
        'code'       => 'S54_QUESTIONS_NOT_FOUND',
        'message'    => 'Questions not found in survey.',
        'statusCode' => 400,
    ];

    public const S54_NUMBER_ALREADY_GHOSTED = [
        'code'       => 'S54_NUMBER_ALREADY_GHOSTED',
        'message'    => 'Number already ghosted.',
        'statusCode' => 400,
    ];

    public const S54_GENDER_CHANGE = [
        'code'       => 'S54_GENDER_CHANGE',
        'message'    => 'Gender cannot be changed.',
        'statusCode' => 400,
    ];

    public const S54_RACE_CHANGE = [
        'code'       => 'S54_RACE_CHANGE',
        'message'    => 'Race cannot be changed.',
        'statusCode' => 400,
    ];

    public const S542_DEMOGRAPHIC_INCOMPLETE = [
        'code'       => 'S542_DEMOGRAPHIC_INCOMPLETE',
        'message'    => 'Please complete your demographic profile.',
        'statusCode' => 400,
    ];

    public const S54_EMAIL_CHANGE = [
        'code'       => 'S54_EMAIL_CHANGE',
        'message'    => 'Email cannot be changed. To really change your email please contact ' . self::CONTACT,
        'statusCode' => 400,
    ];

    public const S54_AT_ERROR_SENDING_MESSAGE = [
        'code'       => 'S54_AT_ERROR_SENDING_MESSAGE',
        'message'    => 'SMS sending error. If this problem persists, please contact us at ' . self::CONTACT,
        'statusCode' => 400,
    ];
    public const S54_TOP_UP_ERROR             = [
        'code'       => 'S54_TOP_UP_ERROR',
        'message'    => 'Top up error. If this problem persists, please contact us at ' . self::CONTACT,
        'statusCode' => 400,
    ];

    public const S54_VERIFICATION_RETRY_LIMIT = [
        'code'       => 'S54_VERIFICATION_RETRY_LIMIT',
        'message'    => 'You have reached the retry limit. Please contact us at ' . self::CONTACT,
        'statusCode' => 400,
    ];

    public const S54_EMPTY_NUMBERS_LIST = [
        'code'       => 'S54_EMPTY_NUMBERS_LIST',
        'message'    => 'The numbers list requires at least one number.',
        'statusCode' => 400,
    ];


    //S542 is the error code prefix for reap service
    //<editor-fold desc="Image">
    public const S54_IMAGE_UPLOAD_ERROR = [
        'code'       => 'S54_IMAGE_UPLOAD_ERROR',
        'message'    => 'There was an issue with uploading the image.',
        'statusCode' => 400,
    ];
    public const S54_IMAGE_DELETE_ERROR = [
        'code'       => 'S54_IMAGE_DELETE_ERROR',
        'message'    => 'There was an issue with deleting the image.',
        'statusCode' => 400,
    ];
    //</editor-fold>
    //<editor-fold desc="Token">
    public const S54_ACCESS_TOKEN_INVALID  = [
        'code'       => 'S54_ACCESS_TOKEN_INVALID',
        'message'    => 'Invalid access token. Please login.',
        'statusCode' => 401,
    ];
    public const S54_REFRESH_TOKEN_EXPIRED = [
        'code'       => 'S54_REFRESH_TOKEN_EXPIRED',
        'message'    => 'Refresh token expired. Please login.',
        'statusCode' => 401,
    ];
    //</editor-fold>
    public const S54_ACCOUNT_NOT_FULLY_VERIFIED          = [
        'code'       => 'S54_ACCOUNT_NOT_FULLY_VERIFIED',
        'message'    => 'Your account is not yet fully verified.',
        'statusCode' => 400,
    ];
    public const S54_LOGIN_ATTEMPTS_ERROR                = [
        'code'       => 'S54_LOGIN_ATTEMPTS_ERROR',
        'message'    => 'You have reached the maximum login attempt. Please reset your password via Forgotten Password.',
        'statusCode' => 400,
    ];
    public const S54_USER_NOT_FOUND                      = [
        'code'       => 'S54_USER_NOT_FOUND',
        'message'    => 'User not found.',
        'statusCode' => 400,
    ];
    public const S54_AUTH_VERIFIED_REQUIRED              = [
        'code'       => 'S54_AUTH_VERIFIED_REQUIRED',
        'message'    => 'VERIFIED auth status is required.',
        'statusCode' => 400,
    ];
    public const S54_AUTH_AWAITING_VERIFICATION_REQUIRED = [
        'code'       => 'S54_AUTH_AWAITING_VERIFICATION_REQUIRED',
        'message'    => 'AWAITING_VERIFICATION auth status is required.',
        'statusCode' => 400,
    ];
    public const S54_VERIFICATION_CODE_EXPIRED           = [
        'code'       => 'S54_VERIFICATION_CODE_EXPIRED',
        'message'    => 'The verification code has expired.',
        'statusCode' => 400,
    ];
    public const S54_VERIFICATION_CODE_INVALID           = [
        'code'       => 'S54_VERIFICATION_CODE_INVALID',
        'message'    => 'The verification code is invalid.',
        'statusCode' => 400,
    ];
    public const S54_INVALID_VERIFICATION_TYPE_MOBILE    = [
        'code'       => 'S54_INVALID_VERIFICATION_TYPE_MOBILE',
        'message'    => 'Your previous code was sent to your mobile, please use mobile to complete the flow.',
        'statusCode' => 400,
    ];
    public const S54_INVALID_VERIFICATION_FLOW           = [
        'code'       => 'S54_INVALID_VERIFICATION_FLOW',
        'message'    => 'You can only request new verification when signing up or requesting forgotten password.',
        'statusCode' => 400,
    ];
    public const S54_AUTH_AWAITING_ACTION_REQUIRED       = [
        'code'       => 'S54_AUTH_AWAITING_ACTION_REQUIRED',
        'message'    => 'AWAITING_ACTION auth status is required.',
        'statusCode' => 400,
    ];

    public const S542_RESPONDENT_MARKED_FOR_DELETION = [
        'code'       => 'S542_RESPONDENT_MARKED_FOR_DELETION',
        'message'    => 'Your account is scheduled for deletion. To reactivate please sign up with the same details.',
        'statusCode' => 400,
    ];
    public const S542_RESPONDENT_NOT_FOUND           = [
        'code'       => 'S542_RESPONDENT_NOT_FOUND',
        'message'    => 'Respondent not found.',
        'statusCode' => 400,
    ];
    public const S542_RESPONDENT_NOT_ACTIVATED       = [
        'code'       => 'S542_RESPONDENT_NOT_ACTIVATED',
        'message'    => 'Your account is not yet activated, follow the signup flow to complete activation.',
        'statusCode' => 400,
    ];

    public const S542_USER_ALREADY_REGISTERED_MOBILE = [
        'code'       => 'S542_USER_ALREADY_REGISTERED_MOBILE',
        'message'    => 'A respondent is already registered by that mobile number. You can use the Forgot Password link to reset your password and login.',
        'statusCode' => 400,
    ];
    public const S542_USER_ALREADY_REGISTERED_EMAIL  = [
        'code'       => 'S542_USER_ALREADY_REGISTERED_EMAIL',
        'message'    => 'A respondent is already registered by that email.',
        'statusCode' => 400,
    ];

    //<editor-fold desc="GetResponses">
    public const S542_ANSWERIDS_APPLIES_TO_ONE_QUESTION              = [
        'code'       => 'S542_ANSWERIDS_APPLIES_TO_ONE_QUESTION',
        'message'    => 'AnswerIds can only be used with a question.',
        'statusCode' => 400,
    ];
    public const S542_QUESTION_SEARCH_REQUIRES_SURVEYID              = [
        'code'       => 'S542_QUESTION_SEARCH_REQUIRES_SURVEYID',
        'message'    => 'Question search requires survey id.',
        'statusCode' => 400,
    ];
    public const S542_ANSWER_SEARCH_REQUIRES_SURVEYID_AND_QUESTIONID = [
        'code'       => 'S542_ANSWER_SEARCH_REQUIRES_SURVEYID_AND_QUESTIONID',
        'message'    => 'Answer search requires survey id and question id.',
        'statusCode' => 400,
    ];
    //</editor-fold>

    //<editor-fold desc="Response from Survey">
    public const S542_INVALID_RESPONSE_LINK   = [
        'code'       => 'S542_INVALID_RESPONSE_LINK',
        'message'    => 'Survey response link is invalid.',
        'statusCode' => 400,
    ];
    public const S542_RESPONDENT_ID_NOT_IN_DB = [
        'code'       => 'S542_RESPONDENT_ID_NOT_IN_DB',
        'message'    => 'Your respondent id is not in our records.',
        'statusCode' => 400,
    ];
    //</editor-fold>


    //<editor-fold desc="Survey">
    public const S542_LAUNCHED_SURVEY_NOT_FOUND             = [
        'code'       => 'S542_LAUNCHED_SURVEY_NOT_FOUND',
        'message'    => 'Launched survey not found.',
        'statusCode' => 400,
    ];
    public const S542_SURVEY_NOT_FOUND                      = [
        'code'       => 'S542_SURVEY_NOT_FOUND',
        'message'    => 'Survey not found.',
        'statusCode' => 400,
    ];
    public const S542_LAUNCHED_SURVEY_EXPECTED              = [
        'code'       => 'S542_LAUNCHED_SURVEY_EXPECTED',
        'message'    => 'Survey must be launched.',
        'statusCode' => 400,
    ];
    public const S542_SURVEY_LAUNCH_EXPECTS_WEB             = [
        'code'       => 'S542_SURVEY_LAUNCH_EXPECTS_WEB',
        'message'    => 'Survey type must be WEB for this launch to succeed.',
        'statusCode' => 400,
    ];
    public const S542_USSD_CODE_REQUIRED                    = [
        'code'       => 'S542_USSD_CODE_REQUIRED',
        'message'    => 'USSD Code is required.',
        'statusCode' => 400,
    ];
    public const S542_SURVEY_COMPLETED                      = [
        'code'       => 'S542_SURVEY_COMPLETED',
        'message'    => 'This survey is complete and no more open for responses.',
        'statusCode' => 400,
    ];
    public const S542_YOU_HAVE_ALREADY_COMPLETED_THE_SURVEY = [
        'code'       => 'S542_YOU_HAVE_ALREADY_COMPLETED_THE_SURVEY',
        'message'    => 'You have already completed this survey.',
        'statusCode' => 400,
    ];
    public const S542_EMAIL_ALREADY_EXIST                   = [
        'code'       => 'S542_EMAIL_ALREADY_EXIST',
        'message'    => 'This email is already registered to a user.',
        'statusCode' => 400,
    ];
    public const S542_MOBILE_ALREADY_EXIST                  = [
        'code'       => 'S542_MOBILE_ALREADY_EXIST',
        'message'    => 'This mobile is already registered to a user.',
        'statusCode' => 400,
    ];
    public const S542_SINGLE_CHOICE_QUESTION_REQUIREMENT    = [
        'code'       => 'S542_SINGLE_CHOICE_QUESTION_REQUIREMENT',
        'message'    => 'This question type requires single choice.',
        'statusCode' => 400,
    ];
    public const S542_MULTIPLE_CHOICE_QUESTION_REQUIREMENT  = [
        'code'       => 'S542_MULTIPLE_CHOICE_QUESTION_REQUIREMENT',
        'message'    => 'This question type requires one or more choices.',
        'statusCode' => 400,
    ];
    public const S542_OPEN_ENDED_QUESTION_REQUIREMENT       = [
        'code'       => 'S542_OPEN_ENDED_QUESTION_REQUIREMENT',
        'message'    => 'This question type requires an open-ended response.',
        'statusCode' => 400,
    ];
    public const S542_SCALE_QUESTION_REQUIREMENT            = [
        'code'       => 'S542_SCALE_QUESTION_REQUIREMENT',
        'message'    => 'This question type requires a number within the scale as response.',
        'statusCode' => 400,
    ];
    public const S542_RANKING_QUESTION_REQUIREMENT          = [
        'code'       => 'S542_RANKING_QUESTION_REQUIREMENT',
        'message'    => 'This question type does not meet ranking requirements.',
        'statusCode' => 400,
    ];
    public const S542_UNSUPPORTED_QUESTION_TYPE             = [
        'code'       => 'S542_UNSUPPORTED_QUESTION_TYPE',
        'message'    => 'Unsupported question type.',
        'statusCode' => 400,
    ];
    public const S542_INVALID_COUNTRY                       = [
        'code'       => 'S542_INVALID_COUNTRY',
        'message'    => 'The country you have entered is not supported.',
        'statusCode' => 400,
    ];
    public const S542_SAME_PASSWORD_ERROR                   = [
        'code'       => 'S542_SAME_PASSWORD_ERROR',
        'message'    => 'The new password should be a different password. Same password used.',
        'statusCode' => 400,
    ];
    public const S542_INVALID_EMAIL_ADDRESS                 = [
        'code'       => 'S542_INVALID_EMAIL_ADDRESS',
        'message'    => 'You have entered an invalid email address',
        'statusCode' => 400,
    ];
    //</editor-fold>

    //<editor-fold desc="Group">
    public const S542_INVALID_GROUP_TYPE                 = [
        'code'       => 'S542_INVALID_GROUP_TYPE',
        'message'    => 'You have entered an invalid group type',
        'statusCode' => 400,
    ];
    //</editor-fold>
}
