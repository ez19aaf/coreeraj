<?php

namespace Survey54\Reap\Domain;

use Survey54\Library\Domain\Domain;
use Survey54\Library\Domain\Values\IntegrationType;
use Survey54\Library\Domain\Values\RespondentSurveyStatus;

class RespondentSurvey extends Domain
{
    public string $respondentId;
    public string $surveyId;
    public string $status = RespondentSurveyStatus::STARTED;
    public ?int $nextQuestionId = null;
    public ?string $ipAddress = null;
    public bool $redeemed = false;
    public ?array $proof = null;
    public bool $errored = false;
    public ?array $error = null;
    public ?array $gotoMap = null;
    public ?string $entryLink = null;
    public ?string $integrationType = IntegrationType::NONE;

    /**
     * RespondentSurvey constructor.
     * @param string $uuid
     * @param string $respondentId
     * @param string $surveyId
     * @param string|null $createdAt
     * @param string|null $updatedAt
     */
    public function __construct(
        string $uuid,
        string $respondentId,
        string $surveyId,
        ?string $createdAt = null,
        ?string $updatedAt = null
    ) {
        parent::__construct($uuid, $createdAt, $updatedAt);
        $this->respondentId = $respondentId;
        $this->surveyId     = $surveyId;
    }

    public static function build(array $data, bool $buildByAlias = false): self
    {
        if ($buildByAlias) {
            $data = self::buildByAlias('respondent_survey_', $data);
        }
        $rs                  = new self(
            $data['uuid'],
            $data['respondentId'],
            $data['surveyId'],
            $data['createdAt'] ?? null,
            $data['updatedAt'] ?? null
        );
        $rs->status          = $data['status'] ?? RespondentSurveyStatus::STARTED;
        $rs->nextQuestionId  = $data['nextQuestionId'] ?? null;
        $rs->ipAddress       = $data['ipAddress'] ?? null;
        $rs->redeemed        = $data['redeemed'] ?? false;
        $rs->proof           = $data['proof'] ?? null;
        $rs->errored         = $data['errored'] ?? false;
        $rs->error           = $data['error'] ?? null;
        $rs->gotoMap         = $data['gotoMap'] ?? null;
        $rs->entryLink       = $data['entryLink'] ?? null;
        $rs->integrationType = $data['integrationType'] ?? IntegrationType::NONE;
        return $rs;
    }

    public function jsonSerialize()
    {
        return [
            'uuid'            => $this->uuid,
            'respondentId'    => $this->respondentId,
            'surveyId'        => $this->surveyId,
            'status'          => $this->status,
            'nextQuestionId'  => $this->nextQuestionId,
            'ipAddress'       => $this->ipAddress,
            'redeemed'        => $this->redeemed,
            'proof'           => $this->proof,
            'errored'         => $this->errored,
            'error'           => $this->error,
            'gotoMap'         => $this->gotoMap,
            'entryLink'       => $this->entryLink,
            'integrationType' => $this->integrationType,
            'createdAt'       => $this->createdAt,
            'updatedAt'       => $this->updatedAt,
        ];
    }
}
