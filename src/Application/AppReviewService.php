<?php

namespace Survey54\Reap\Application;

use Survey54\Library\Utilities\DateTime;
use Survey54\Reap\Application\Repository\AppReviewRepository;
use Survey54\Reap\Application\Repository\RespondentRepository;
use Survey54\Reap\Domain\AppReview;
use Survey54\Reap\Domain\Respondent;
use Survey54\Reap\Framework\Exception\Error;

class AppReviewService
{
    private AppReviewRepository $appReviewRepository;
    private RespondentRepository $respondentRepository;

    /**
     * AppReviewService constructor.
     * @param AppReviewRepository $appReviewRepository
     * @param RespondentRepository $respondentRepository
     */
    public function __construct(AppReviewRepository $appReviewRepository, RespondentRepository $respondentRepository)
    {
        $this->appReviewRepository  = $appReviewRepository;
        $this->respondentRepository = $respondentRepository;
    }

    /**
     * @param array $data
     * @return AppReview
     */
    public function createOrUpdate(array $data): AppReview
    {
        /** @var Respondent $respondent */
        if (!$respondent = $this->respondentRepository->find($data['uuid'])) {
            Error::throwError(Error::S542_RESPONDENT_NOT_FOUND);
        }

        // If respondent review exist, then update else create
        // Note that uuid in appReview table is respondent uuid
        /** @var AppReview $appReview */
        if ($appReview = $this->appReviewRepository->find($data['uuid'])) {
            // Set promptReview to false
            $respondent->promptReview = false;
            $this->respondentRepository->update($respondent);
            // Update
            $appReview->dontShow  = $data['dontShow'] ?? false;
            $appReview->updatedAt = DateTime::generate();
            $this->appReviewRepository->update($appReview);
        } else {
            // Set promptReview to true
            $respondent->promptReview = true;
            $this->respondentRepository->update($respondent);
            // Create
            // (uuid is respondent uuid)
            $data['dontShow']  = $data['dontShow'] ?? false;
            $data['createdAt'] = $data['updatedAt'] = DateTime::generate();
            $appReview         = AppReview::build($data);
            $this->appReviewRepository->add($appReview);
        }
        return $appReview;
    }

    /**
     * Used during respondent login
     * @param string $uuid
     */
    public function promptReview(string $uuid): void
    {
        /** @var Respondent $respondent */
        if (!$respondent = $this->respondentRepository->find($uuid)) {
            Error::throwError(Error::S542_RESPONDENT_NOT_FOUND);
        }

        /** @var AppReview $appReview */
        if (!$appReview = $this->appReviewRepository->find($uuid)) {
            // Create
            $this->createOrUpdate([
                'uuid'     => $respondent->uuid,
                'dontShow' => false,
            ]);
            return;
        }

        // dontShow === false && last update is from 2 months ago :
        // Set respondent.promptReview = true; Else: false;
        if (!$appReview->dontShow && strtotime($appReview->updatedAt) <= strtotime('-60 days')) {
            $respondent->promptReview = true;
        } else {
            $respondent->promptReview = false;
        }
        $this->respondentRepository->update($respondent);
    }
}
