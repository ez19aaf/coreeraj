<?php

use Phinx\Seed\AbstractSeed;
use Survey54\Library\Domain\Values\AgeGroup;
use Survey54\Library\Domain\Values\Employment;
use Survey54\Library\Domain\Values\Gender;
use Survey54\Library\Domain\Values\SurveyStatus;
use Survey54\Library\Utilities\DateTime;

class SurveySeeder extends AbstractSeed
{
    /**
     * Run Method.
     *
     * Write your database seeder using this method.
     *
     * More information on writing seeders is available here:
     * http://docs.phinx.org/en/latest/seeding.html
     */
    public function run(): void
    {
        $survey = $this->table('survey');
        $data   = $this->data();

        foreach ($data as $item) {
            $survey->insert($item)->save();
        }
    }

    /**
     * @return array
     */
    private function data(): array
    {
        $createdAt = DateTime::generate();
        return [
            [
                'uuid'              => 'ab3163e8-73a3-42d2-90e8-09d32bd52410',
                'userId'            => 'a16a9a79-5f05-4b22-8b2e-a2d017a9adc0',
                'title'             => 'Sample 1',
                'description'       => 'The first description',
                'expectedCompletes' => 100,
                'countries'         => json_encode(['Nigeria'], JSON_THROW_ON_ERROR),
                'questions'         => $this->encodeFromFile(__DIR__ . '/questions/SampleQuestions1.json'),
                'sample'            => json_encode([
                    'ageGroup'   => AgeGroup::toArray(),
                    'gender'     => Gender::toArray(),
                    'employment' => Employment::toArray(),
                ], JSON_THROW_ON_ERROR),
                'image'             => 'https://cdn.pixabay.com/photo/2017/06/13/13/49/indoor-2398938_960_720.jpg',
                'tagLabels'         => json_encode(['Health'], JSON_THROW_ON_ERROR),
                'incentive'         => 250,
                'incentiveCurrency' => 'NGN',
                'status'            => SurveyStatus::LAUNCHED,
                'createdAt'         => $createdAt,
            ],
            [
                'uuid'              => 'ab3163e8-73a3-42d2-90e8-09d32bd52411',
                'userId'            => 'a16a9a79-5f05-4b22-8b2e-a2d017a9adc0',
                'title'             => 'Sample 2',
                'description'       => 'The second description',
                'expectedCompletes' => 100,
                'countries'         => json_encode(['Nigeria'], JSON_THROW_ON_ERROR),
                'questions'         => $this->encodeFromFile(__DIR__ . '/questions/SampleQuestions2.json'),
                'sample'            => json_encode([
                    'ageGroup'   => AgeGroup::toArray(),
                    'gender'     => Gender::toArray(),
                    'employment' => Employment::toArray(),
                ], JSON_THROW_ON_ERROR),
                'image'             => 'https://cdn.pixabay.com/photo/2016/07/10/19/19/interior-design-1508276_960_720.jpg',
                'tagLabels'         => json_encode(['Education', 'Health'], JSON_THROW_ON_ERROR),
                'incentive'         => 250,
                'incentiveCurrency' => 'NGN',
                'status'            => SurveyStatus::LAUNCHED,
                'createdAt'         => $createdAt,
            ],
        ];
    }

    protected function encodeFromFile(string $jsonFile): string
    {
        return json_encode(json_decode(file_get_contents($jsonFile), true, 512, JSON_THROW_ON_ERROR), JSON_THROW_ON_ERROR);
    }
}
