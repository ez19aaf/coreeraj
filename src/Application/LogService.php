<?php

namespace Survey54\Reap\Application;

use Survey54\Library\Helper\SearchBuilder;
use Survey54\Reap\Application\Repository\LogRepository;

class LogService
{
    private LogRepository $logRepository;

    /**
     * LogService constructor.
     * @param LogRepository $logRepository
     */
    public function __construct(LogRepository $logRepository)
    {
        $this->logRepository = $logRepository;
    }

    /**
     * @param array $data
     * @return array
     */
    public function list(array $data): array
    {
        return $this->logRepository->list($data['offset'], $data['limit'], $this->buildSearch($data));
    }

    /**
     * @param array $data
     * @return int
     */
    public function count(array $data): int
    {
        return $this->logRepository->count($this->buildSearch($data));
    }

    /**
     * @param array $data
     * @return array|null
     */
    private function buildSearch(array $data): ?array
    {
        $builder = new SearchBuilder($data);
        $builder->addTerm('objectId', 'EQUALS');
        $builder->addTerm('objectType', 'EQUALS');
        $builder->addTerm('action', 'EQUALS');
        return $builder->getSearch();
    }
}
