<?php

use Phinx\Seed\AbstractSeed;
use Survey54\Library\Utilities\DateTime;
use Survey54\Library\Domain\Values\AgeGroup;
use Survey54\Library\Domain\Values\Employment;
use Survey54\Library\Domain\Values\Gender;

class RespondentSeeder extends AbstractSeed
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
        $respondent = $this->table('respondent');
        $data       = $this->data();

        foreach ($data as $item) {
            $respondent->insert($item)->save();
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
                'uuid'       => 'a16a9a79-5f05-4b22-8b2e-a2d017a9ade0',
                'mobile'     => '+254791350402',
                'ageGroup'   => AgeGroup::AGE_16_17,
                'employment' => Employment::EMPLOYED,
                'gender'     => Gender::MALE,
                'country'    => 'Nigeria',
                'region'     => 'Port Harcourt',
                'isCint'     => 0,
                'createdAt'  => $createdAt,
            ],
        ];
    }
}
