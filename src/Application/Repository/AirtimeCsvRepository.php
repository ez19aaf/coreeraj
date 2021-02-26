<?php


namespace Survey54\Reap\Application\Repository;

use Survey54\Library\Repository\Repository;

class AirtimeCsvRepository extends Repository
{
    protected function postRead(array &$data): void
    {
        $this->decode($data['error']);
        $this->decode($data['proof']);
        $data['errored']  = (bool)$data['errored'];
        $data['redeemed'] = (bool)$data['redeemed'];
    }

    protected function preWrite(array &$data): void
    {
        $this->encode($data['error']);
        $this->encode($data['proof']);
        $data['errored']  = $data['errored'] ? 1 : 0;
        $data['redeemed'] = $data['redeemed'] ? 1 : 0;
    }

    public function list(int $offset, int $limit, ?array $search = null, ?array $sort = null, ?string $select = null): array
    {
        $airtimeLogs = parent::list($offset, $limit, $search, $sort, $select);

        if ($select !== null) {
            $arr = explode(',', $select);
            if (!(in_array('error', $arr, true) &&
                in_array('errored', $arr, true) &&
                in_array('proof', $arr, true) &&
                in_array('redeemed', $arr, true)
            )) {
                return $airtimeLogs;
            }
        }

        if ($airtimeLogs) {
            foreach ($airtimeLogs as &$data) {
                $this->postRead($data);
            }
        }
        return $airtimeLogs;
    }
}
