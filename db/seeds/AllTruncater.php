<?php

use Phinx\Seed\AbstractSeed;

class AllTruncater extends AbstractSeed
{
    /**
     * Run Method.
     *
     * Write your database seeder using this method.
     *
     * More information on writing seeders is available here:
     * http://docs.phinx.org/en/latest/seeding.html
     */
    public function run()
    {
        $this->execute('SET FOREIGN_KEY_CHECKS = 0');
        $this->table('respondent')->truncate();
        $this->table('respondent_survey')->truncate();
        $this->table('response')->truncate();
        $this->table('survey')->truncate();
        $this->table('insight')->truncate();
        $this->execute('SET FOREIGN_KEY_CHECKS = 1');
    }
}
