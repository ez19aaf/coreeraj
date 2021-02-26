<?php


namespace Survey54\Reap\Application;

use Survey54\Library\Domain\Values\AgeGroup;
use Survey54\Library\Domain\Values\AuthStatus;
use Survey54\Library\Domain\Values\Employment;
use Survey54\Library\Domain\Values\Gender;
use Survey54\Library\Domain\Values\GroupType;
use Survey54\Library\Domain\Values\LsmGroup;
use Survey54\Library\Domain\Values\Race;
use Survey54\Library\Domain\Values\UserStatus;
use Survey54\Library\Utilities\DateTime;
use Survey54\Library\Utilities\UUID;
use Survey54\Reap\Application\Repository\GroupRepository;
use Survey54\Reap\Application\Repository\RespondentRepository;
use Survey54\Reap\Domain\Group;
use Survey54\Reap\Framework\Exception\Error;

class GroupService
{
    private GroupRepository $groupRepository;
    private RespondentRepository $respondentRepository;


    /**
     * GhostService constructor.
     * @param GroupRepository $groupRepository
     * @param RespondentRepository $respondentRepository
     */
    public function __construct(
        GroupRepository $groupRepository,
        RespondentRepository $respondentRepository
    ) {
        $this->groupRepository      = $groupRepository;
        $this->respondentRepository = $respondentRepository;
    }

    /**
     * @param array $data
     * @return array
     */
    public function create(array $data): array
    {
        $mobileList = [];
        if (!in_array($data['groupType'], GroupType::toArray())) {
            Error::throwError(Error::S542_INVALID_GROUP_TYPE);
        }

        if ($data['groupType'] == GroupType::DEMOGRAPHIC) {
            $mobileList = $this->searchRespondent($data);
        } elseif ($data['groupType'] == GroupType::UPLOADED) {
            $respondent = [];
            foreach ($data['audience'] as $key => $value) {
                $value['uuid']       = UUID::generate();
                $value['createdAt']  = DateTime::generate();
                $value['isSample' ]  = 0;
                $value['userStatus'] = UserStatus::PENDING;
                $value['authStatus'] = AuthStatus::UNVERIFIED;

                $respondent[$key] = $value;
                $mobileList[$key] = $value['mobile'];
            }

            $this->respondentRepository->addBulk($respondent);
        }
        $group = $this->groupData($data, $mobileList);
        $group = Group::build($group);
        $group = $this->groupRepository->add($group);
        return ["group"=> $group];
    }

    /**
     * @param array $data
     * @return array
     */
    public function update(array $data): array
    {
        if (!$group = $this->groupRepository->find($data['uuid'])) {
            Error::throwError(Error::S54_RESOURCE_NOT_FOUND);
        }

        $mobileList = $this->searchRespondent($data);
        $group['audience']  = $mobileList;
        $group['updatedAt'] = DateTime::generate();
        $group = Group::build((array)$group);
        $response = $this->groupRepository->update($group);
        return ['group' => $response];
    }

    /**
     * @param array $data
     */
    public function delete(array $data): void
    {
        if (empty($data['uuid'])) {
            Error::throwError(Error::S54_RESOURCE_NOT_FOUND);
        }
        foreach ($data['uuid'] as $uuid) {
            if ($this->groupRepository->find($uuid)) {
                $this->groupRepository->delete($uuid);
            }
        }
    }


    /**
     * @param array $data
     * @return array
     */
    public function getGroups(array $data): array
    {

        $search = [
            'userId'       => ['EQUALS', $data['userId']],
        ];
        return $this->groupRepository->list($data['offset'], $data['limit'], $search, null, 'uuid, groupName');
    }


    /**
     * @param array $data
     * @return array
     */
    public function searchRespondent($data): array
    {
        $search = [
            'country'       => ['EQUALS', $data['country']],
            'ageGroup'      => ['IN', AgeGroup::toArray()],
            'gender'        => ['IN', Gender::toArray()],
            'employment'    => ['IN', Employment::toArray()],
        ];

        if (!empty($data['lsmGroup'])) {
            $search['lsmGroup'] = ['IN', LsmGroup::toArray()];
        }
        if (!empty($data['race'])) {
            $search['race'] = ['IN', Race::toArray()];
        }

        $mList = [];
        $mobileList = $this->respondentRepository->list(0, 500, $search, null, 'mobile');
        foreach ($mobileList as $index => $respondent) {
            if (isset($respondent['mobile'])) {
                $mList[$index] = $respondent['mobile'];
            }
        }
        $count = count($mobileList);
        if ($count === 0) {
            Error::throwError(Error::S542_RESPONDENT_NOT_FOUND);
        }
        return $mList;
    }

    /**
     * @param array $data
     * @param array|null $audienceMobile
     * @return array
     */
    public function groupData($data, $audienceMobile = null)
    {
        $group              = [];
        $group['uuid']      = UUID::generate();
        $group['groupName'] = $data['groupName'];
        $group['userId']    = $data['userId'];
        $group['audience']  = $audienceMobile;
        $group['groupType'] = $data['groupType'];
        $group['recurrence'] = $data['recurrence'];
        $group['createdAt'] = DateTime::generate();

        return $group;
    }
}
