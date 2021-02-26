<?php

namespace Survey54\Reap\Domain;

use Survey54\Library\Domain\Domain;
use Survey54\Library\Domain\Values\SurveyPushTo;
use Survey54\Library\Domain\Values\SurveyStatus;
use Survey54\Library\Domain\Values\SurveyType;

class Survey extends Domain
{
    public string $userId;
    public string $title;
    public ?string $description = null;
    public string $type = SurveyType::WEB;
    public int $expectedCompletes = 100;
    public int $actualCompletes = 0;
    public int $actualCompletesPercent = 0;
    public ?array $countries = null;
    public ?array $sample = null;
    public ?array $questions = null;
    public ?string $image = null;
    public ?string $groupId = null;
    public ?array $audience = null;
    public ?array $tagIds = null;
    public ?array $tagLabels = null;
    public bool $favourite = false;
    public string $status = SurveyStatus::INACTIVE;
    public ?string $orderId = null;
    public int $countScreeningQuestions = 0;
    public int $incidentRate = 0;
    public int $lengthOfInterview = 0;
    public int $incentive = 0;
    public ?string $incentiveCurrency = null;
    public ?string $smsCode = null;
    public ?string $ussdCode = null;
    public ?string $category = null;
    public ?string $subject = null;
    public ?array $recurrence = null;
    public bool $pushNotification = false;
    public string $pushTo = SurveyPushTo::LIVE;

    public function __construct(
        string $uuid,
        string $userId,
        string $title,
        ?string $createdAt = null,
        ?string $updatedAt = null
    ) {
        parent::__construct($uuid, $createdAt, $updatedAt);
        $this->userId = $userId;
        $this->title  = $title;
    }

    public static function build(array $data, bool $buildByAlias = false)
    {
        if ($buildByAlias) {
            $data = self::buildByAlias('survey_', $data);
        }
        $survey = new self(
            $data['uuid'],
            $data['userId'],
            $data['title'],
            $data['createdAt'] ?? null,
            $data['updatedAt'] ?? null
        );

        $survey->description             = $data['description'] ?? null;
        $survey->type                    = $data['type'] ?? SurveyType::WEB;
        $survey->expectedCompletes       = $data['expectedCompletes'] ?? 100;
        $survey->actualCompletes         = $data['actualCompletes'] ?? 0;
        $survey->actualCompletesPercent  = $data['actualCompletesPercent'] ?? 0;
        $survey->countries               = $data['countries'] ?? null;
        $survey->sample                  = $data['sample'] ?? null;
        $survey->questions               = $data['questions'] ?? null;
        $survey->image                   = $data['image'] ?? null;
        $survey->groupId                 = $data['groupId'] ?? null;
        $survey->audience                = $data['audience'] ?? null;
        $survey->tagIds                  = $data['tagIds'] ?? null;
        $survey->tagLabels               = $data['tagLabels'] ?? null;
        $survey->favourite               = $data['favourite'] ?? false;
        $survey->status                  = $data['status'] ?? SurveyStatus::INACTIVE;
        $survey->orderId                 = $data['orderId'] ?? null;
        $survey->countScreeningQuestions = $data['countScreeningQuestions'] ?? 0;
        $survey->incidentRate            = $data['incidentRate'] ?? 0;
        $survey->lengthOfInterview       = $data['lengthOfInterview'] ?? 0;
        $survey->incentive               = $data['incentive'] ?? 0;
        $survey->incentiveCurrency       = $data['incentiveCurrency'] ?? null;
        $survey->smsCode                 = $data['smsCode'] ?? null;
        $survey->ussdCode                = $data['ussdCode'] ?? null;
        $survey->category                = $data['category'] ?? null;
        $survey->subject                 = $data['subject'] ?? null;
        $survey->recurrence              = $data['recurrence'] ?? null;
        $survey->pushNotification        = $data['pushNotification'] ?? false;
        $survey->pushTo                  = $data['pushTo'] ?? SurveyPushTo::LIVE;

        return $survey;
    }

    public function jsonSerialize()
    {
        return [
            'uuid'                    => $this->uuid,
            'userId'                  => $this->userId,
            'title'                   => $this->title,
            'description'             => $this->description,
            'type'                    => $this->type,
            'expectedCompletes'       => $this->expectedCompletes,
            'actualCompletes'         => $this->actualCompletes,
            'actualCompletesPercent'  => $this->actualCompletesPercent,
            'countries'               => $this->countries,
            'sample'                  => $this->sample,
            'questions'               => $this->questions,
            'image'                   => $this->image,
            'groupId'                 => $this->groupId,
            'audience'                => $this->audience,
            'tagIds'                  => $this->tagIds,
            'tagLabels'               => $this->tagLabels,
            'favourite'               => $this->favourite,
            'status'                  => $this->status,
            'orderId'                 => $this->orderId,
            'countScreeningQuestions' => $this->countScreeningQuestions,
            'incidentRate'            => $this->incidentRate,
            'lengthOfInterview'       => $this->lengthOfInterview,
            'incentive'               => $this->incentive,
            'incentiveCurrency'       => $this->incentiveCurrency,
            'smsCode'                 => $this->smsCode,
            'ussdCode'                => $this->ussdCode,
            'category'                => $this->category,
            'subject'                 => $this->subject,
            'recurrence'              => $this->recurrence,
            'pushNotification'        => $this->pushNotification,
            'pushTo'                  => $this->pushTo,
            'createdAt'               => $this->createdAt,
            'updatedAt'               => $this->updatedAt,
        ];
    }
}
