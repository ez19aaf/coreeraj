<?php


namespace Survey54\Reap\Application\Repository;

use Survey54\Library\Repository\Repository;

class GroupRepository extends Repository
{
    protected function postRead(array &$data): void
    {
        $this->decode($data['audience']);
    }

    protected function preWrite(array &$data): void
    {
        $this->encode($data['audience']);
    }
}
