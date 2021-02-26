<?php

namespace Survey54\Reap\Application;

use Exception;
use Survey54\Library\Domain\Values\Country;
use Survey54\Library\Helper\SearchBuilder;
use Survey54\Library\Message\TextMessageService;
use Survey54\Library\Utilities\UUID;
use Survey54\Reap\Application\Repository\GdprRepository;
use Survey54\Reap\Application\Repository\GhostRepository;
use Survey54\Reap\Application\Repository\RespondentRepository;
use Survey54\Reap\Application\Repository\RespondentSurveyRepository;
use Survey54\Reap\Application\Repository\ResponseRepository;
use Survey54\Reap\Domain\Ghost;
use Survey54\Reap\Framework\Exception\Error;

class GhostService
{
    private GdprRepository $gdprRepository;
    private GhostRepository $ghostRepository;
    private RespondentRepository $respondentRepository;
    private RespondentSurveyRepository $respondentSurveyRepository;
    private ResponseRepository $responseRepository;
    private TextMessageService $textMessageService;

    /**
     * GhostService constructor.
     * @param GdprRepository $gdprRepository
     * @param GhostRepository $ghostRepository
     * @param RespondentRepository $respondentRepository
     * @param RespondentSurveyRepository $respondentSurveyRepository
     * @param ResponseRepository $responseRepository
     * @param TextMessageService $textMessageService
     */
    public function __construct(
        GdprRepository $gdprRepository,
        GhostRepository $ghostRepository,
        RespondentRepository $respondentRepository,
        RespondentSurveyRepository $respondentSurveyRepository,
        ResponseRepository $responseRepository,
        TextMessageService $textMessageService
    ) {
        $this->gdprRepository             = $gdprRepository;
        $this->ghostRepository            = $ghostRepository;
        $this->respondentRepository       = $respondentRepository;
        $this->respondentSurveyRepository = $respondentSurveyRepository;
        $this->responseRepository         = $responseRepository;
        $this->textMessageService         = $textMessageService;
    }

    /**
     * @param int $length
     * @return string
     */
    private function randomNumber(int $length): string
    {
        $characters       = '123456789';
        $charactersLength = strlen($characters);
        $randomString     = '';
        try {
            for ($i = 0; $i < $length; $i++) {
                $randomString .= $characters[random_int(0, $charactersLength - 1)];
            }
        } catch (Exception $e) {
            Error::throwError(Error::S54_INTERNAL_SERVER_ERROR, $e->getMessage());
        }
        return $randomString;
    }

    /**
     * @param array $data
     * @return array
     */
    public function create(array $data): array
    {
        $fakes = [];

        foreach ($data['ghostIntoCountries'] as $ghostCountry) {
            do {
                switch ($ghostCountry) {
                    case Country::GHANA:
                        $data['mobile'] = '+233' . $this->randomNumber(4) . '00000';
                        break;
                    case Country::KENYA:
                        $data['mobile'] = '+254' . $this->randomNumber(4) . '00000';
                        break;
                    case Country::NIGERIA:
                        $data['mobile'] = '+234' . $this->randomNumber(5) . '00000';
                        break;
                    case Country::SOUTH_AFRICA:
                        $data['mobile'] = '+27' . $this->randomNumber(4) . '00000';
                        break;
                }

                $alreadyGhostedOrRegistered = false;

                if ($this->ghostRepository->findByMobile($data['mobile']) || $this->respondentRepository->findByMobile($data['mobile'])) {
                    $alreadyGhostedOrRegistered = true;
                }
            } while ($alreadyGhostedOrRegistered === true);

            $data['uuid'] = UUID::generate();
            $ghost        = Ghost::build($data);
            $this->ghostRepository->add($ghost);
            $this->textMessageService->newGhostNotice($ghost->ghostMobile, $ghost->mobile);

            $fakes[$ghostCountry] = $ghost->mobile;
        }

        return [
            'organisationId' => $data['organisationId'],
            'ghostMobile'    => $data['ghostMobile'],
            'fakeMobiles'    => $fakes,
        ];
    }

    /**
     * @param string $uuid
     */
    public function delete(string $uuid): void
    {
        /** @var Ghost $ghost */
        if (!$ghost = $this->ghostRepository->find($uuid)) {
            Error::throwError(Error::S54_RESOURCE_NOT_FOUND);
        }

        if (!$respondent = $this->respondentRepository->findByMobile($ghost->mobile)) {
            $this->ghostRepository->delete($ghost->uuid);
            return;
        }

        $search     = [
            'respondentId' => ['EQUALS', $respondent->uuid],
        ];
        $gdprSearch = [
            'userId' => ['EQUALS', $respondent->uuid],
        ];

        // Delete responses by ghost
        $this->responseRepository->deleteBy($search);
        // Delete respondentSurvey by ghost
        $this->respondentSurveyRepository->deleteBy($search);
        // Delete gdpr by ghost
        $this->gdprRepository->deleteBy($gdprSearch);
        // Delete respondent by ghost
        $this->respondentRepository->delete($respondent->uuid);
        // Delete ghost
        $this->ghostRepository->delete($ghost->uuid);
    }

    /**
     * @param array $data
     * @return array
     */
    public function list(array $data): array
    {
        return $this->ghostRepository->list($data['offset'], $data['limit'], $this->buildSearch($data));
    }

    /**
     * @param array $data
     * @return int
     */
    public function count(array $data): int
    {
        return $this->ghostRepository->count($this->buildSearch($data));
    }

    /**
     * @param array $data
     * @return array|null
     */
    private function buildSearch(array $data): ?array
    {
        $builder = new SearchBuilder($data);
        $builder->addTerm('organisationId', 'EQUALS');
        return $builder->getSearch();
    }
}
