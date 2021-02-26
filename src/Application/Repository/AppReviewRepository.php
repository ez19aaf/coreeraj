<?php


namespace Survey54\Reap\Application\Repository;

use Survey54\Library\Repository\Repository;

class AppReviewRepository extends Repository
{
    protected function postRead(array &$data): void
    {
        $data['dontShow'] = (bool)$data['dontShow'];
    }

    protected function preWrite(array &$data): void
    {
        $data['dontShow'] = $data['dontShow'] ? 1 : 0;
    }

    public function list(int $offset, int $limit, ?array $search = null, ?array $sort = null, ?string $select = null): array
    {
        $appReview = parent::list($offset, $limit, $search, $sort, $select);

        if ($select !== null) {
            $arr = explode(',', $select);
            if (!in_array('dontShow', $arr, true)) {
                return $appReview;
            }
        }

        if ($appReview) {
            foreach ($appReview as &$data) {
                $this->postRead($data);
            }
        }
        return $appReview;
    }
}
