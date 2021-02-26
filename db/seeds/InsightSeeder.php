<?php

use Phinx\Seed\AbstractSeed;
use Survey54\Library\Utilities\DateTime;

class InsightSeeder extends AbstractSeed
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
        $insight = $this->table('insight');
        $data    = $this->data();

        foreach ($data as $item) {
            $insight->insert($item)->save();
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
                'uuid'      => 'e23a9a79-5f05-4b22-8b2e-a2d017a9aeb0',
                'userId'    => 'a16a9a79-5f05-4b22-8b2e-a2d017a9adc0',
                'surveyId'  => 'ab3163e8-73a3-42d2-90e8-09d32bd52410',
                'summary'   => 'This is the first summary.',
                'createdAt' => $createdAt,
            ],
            [
                'uuid'      => 'e23a9a79-5f05-4b22-8b2e-a2d017a9aeb1',
                'userId'    => 'a16a9a79-5f05-4b22-8b2e-a2d017a9adc0',
                'surveyId'  => 'ab3163e8-73a3-42d2-90e8-09d32bd52410',
                'summary'   => 'This is the second summary.',
                'createdAt' => $createdAt,
            ],
            [
                'uuid'      => 'e23a9a79-5f05-4b22-8b2e-a2d017a9aeb2',
                'userId'    => 'a16a9a79-5f05-4b22-8b2e-a2d017a9adc0',
                'surveyId'  => 'ab3163e8-73a3-42d2-90e8-09d32bd52410',
                'summary'   => 'This is the third summary.',
                'createdAt' => $createdAt,
            ],
            [
                'uuid'      => 'e23a9a79-5f05-4b22-8b2e-a2d017a9aeb3',
                'userId'    => 'a16a9a79-5f05-4b22-8b2e-a2d017a9adc0',
                'surveyId'  => 'ab3163e8-73a3-42d2-90e8-09d32bd52410',
                'summary'   => 'This is the fourth summary.',
                'createdAt' => $createdAt,
            ],
            [
                'uuid'      => 'e23a9a79-5f05-4b22-8b2e-a2d017a9aeb4',
                'userId'    => 'a16a9a79-5f05-4b22-8b2e-a2d017a9adc0',
                'surveyId'  => 'ab3163e8-73a3-42d2-90e8-09d32bd52410',
                'summary'   => 'This is the fifth summary.',
                'createdAt' => $createdAt,
            ],
        ];
    }
}
