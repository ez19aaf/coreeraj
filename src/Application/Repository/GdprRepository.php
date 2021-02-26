<?php

namespace Survey54\Reap\Application\Repository;

use Survey54\Library\Repository\Repository;

class GdprRepository extends Repository
{
    public function findByUserId(string $userId)
    {
        $search['userId'] = ['EQUALS', $userId];
        return $this->findBy($search);
    }
}
