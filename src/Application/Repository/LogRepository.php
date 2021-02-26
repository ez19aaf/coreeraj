<?php

namespace Survey54\Reap\Application\Repository;

use Survey54\Library\Repository\Repository;

class LogRepository extends Repository
{
    protected function postRead(array &$data): void
    {
        $this->decode($data['request']);
        $this->decode($data['response']);
    }

    protected function preWrite(array &$data): void
    {
        $this->encode($data['request']);
        $this->encode($data['response']);
    }

    /**
     * @param int $offset
     * @param int $limit
     * @param array|null $search
     * @param array|null $sort
     * @param string|null $select
     * @return array
     */
    public function list(int $offset, int $limit, ?array $search = null, ?array $sort = null, ?string $select = null): array
    {
        $data = $this->adapter->list($offset, $limit, $search, $sort, $select);
        if (count($data) > 0) {
            foreach ($data as &$item) {
                $this->postRead($item);
            }
        }
        return $data;
    }
}
