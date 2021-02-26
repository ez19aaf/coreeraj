<?php

namespace Survey54\Reap\Application\Repository;

use Survey54\Library\Repository\Repository;
use Survey54\Reap\Domain\Ghost;

class GhostRepository extends Repository
{
    /**
     * @param string $mobile
     * @return bool|Ghost
     */
    public function findByMobile(string $mobile)
    {
        $search['mobile'] = ['EQUALS', $mobile];
        return $this->findBy($search);
    }

    /**
     * @param string $organisationId
     * @return array
     */
    public function listGhostMobileByOrganisationId(string $organisationId): array
    {
        $search['organisationId'] = ['EQUALS', $organisationId];
        $list                     = $this->list(0, 0, $search, null, 'ghostMobile');
        return array_column($list, 'ghostMobile');
    }
}
