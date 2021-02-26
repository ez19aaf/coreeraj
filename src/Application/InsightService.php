<?php

namespace Survey54\Reap\Application;

use Survey54\Library\Helper\SearchBuilder;
use Survey54\Library\Utilities\DateTime;
use Survey54\Library\Utilities\UUID;
use Survey54\Reap\Application\Repository\InsightRepository;

class InsightService
{
    private InsightRepository $insightRepository;

    /**
     * InsightService constructor.
     * @param InsightRepository $insightRepository
     */
    public function __construct(InsightRepository $insightRepository)
    {
        $this->insightRepository = $insightRepository;
    }

    /**
     * @param array $data
     * @return array|null
     */
    public function create(array $data): ?array
    {
        $createdAt = DateTime::generate();
        $insight   = [];
        foreach ($data['summary'] as $summary) {
            $insight[] = [
                'uuid'      => UUID::generate(),
                'surveyId'  => $data['surveyId'],
                'userId'    => $data['userId'],
                'summary'   => $summary,
                'createdAt' => $createdAt,
            ];
        }

        // Todo:: clean up errors returned by bulk. Don't return SQL error to prod.
        return $this->insightRepository->addBulk($insight);
    }

    /**
     * @param array $data
     * @return array
     */
    public function list(array $data): array
    {
        return $this->insightRepository->list(0, 20, $this->buildSearch($data));
    }

    /**
     * @param array $data
     * @return int
     */
    public function count(array $data): int
    {
        return $this->insightRepository->count($this->buildSearch($data));
    }

    /**
     * @param array $data
     * @return array|null
     */
    private function buildSearch(array $data): ?array
    {
        $builder = new SearchBuilder($data);
        $builder->addTerm('surveyId', 'EQUALS');
        return $builder->getSearch();
    }
}
