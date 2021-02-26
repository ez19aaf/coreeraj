<?php

namespace Survey54\Reap\Application;

use Survey54\Library\Domain\Values\GdprAction;
use Survey54\Library\Domain\Values\UserStatus;
use Survey54\Library\Domain\Values\UserType;
use Survey54\Library\Utilities\UUID;
use Survey54\Reap\Application\Repository\GdprRepository;
use Survey54\Reap\Application\Repository\RespondentRepository;
use Survey54\Reap\Domain\Gdpr;
use Survey54\Reap\Domain\Respondent;
use Survey54\Reap\Framework\Exception\Error;

class GdprService
{
    private GdprRepository $gdprRepository;
    private RespondentRepository $respondentRepository;

    /**
     * GdprService constructor.
     * @param GdprRepository $gdprRepository
     * @param RespondentRepository $respondentRepository
     */
    public function __construct(
        GdprRepository $gdprRepository,
        RespondentRepository $respondentRepository
    ) {
        $this->gdprRepository       = $gdprRepository;
        $this->respondentRepository = $respondentRepository;
    }

    /**
     * @param string $userId
     */
    public function scheduleDeletion(string $userId): void
    {
        $gdpr = Gdpr::build([
            'uuid'     => UUID::generate(),
            'userId'   => $userId,
            'userType' => UserType::RESPONDENT,
            'action'   => GdprAction::DELETE_ACCOUNT,
            'duration' => 30,
        ]);

        $this->gdprRepository->add($gdpr);
    }

    /**
     * TODO: Add the console script to run this
     * @param string $userId
     */
    public function pseudoDeleteUser(string $userId): void
    {
        /** @var $user Respondent */
        if (!$user = $this->respondentRepository->find($userId)) {
            Error::throwError(Error::S54_USER_NOT_FOUND);
        }

        $count = $this->respondentRepository->count(['userStatus' => ['EQUALS', UserStatus::DELETED]]);

        $user->firstName  = 'gdpr';
        $user->lastName   = 'lname';
        $user->mobile     = '+100000' . ++$count;
        $user->email      = $user->uuid . '@survey54.gdpr';
        $user->userStatus = UserStatus::DELETED;

        $this->respondentRepository->update($user);
    }
}
