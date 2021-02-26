<?php

use Phinx\Db\Table;
use Survey54\Library\Domain\Values\Country;

class PlugTables
{
    private InitialSetup $setup;

    /**
     * @param InitialSetup $setup
     * @return $this
     */
    public function setSetup(InitialSetup $setup): self
    {
        $this->setup = $setup;
        return $this;
    }

    public function create(): void
    {
        $cint = $this->start('cint')
            ->addColumn('name', 'string', ['limit' => 500])
            ->addColumn('cpi', 'decimal', ['null' => true])
            ->addColumn('loi', 'integer', ['null' => true])
            ->addColumn('country', 'enum', ['values' => Country::toArray()])
            ->addColumn('remainingCompletes', 'double', ['null' => true])
            ->addColumn('conversionRate', 'decimal', ['null' => true])
            ->addColumn('matchToQualify', 'boolean', ['null' => true])
            ->addColumn('delayCrediting', 'boolean', ['null' => true])
            ->addColumn('tentativePayout', 'boolean', ['null' => true])
            ->addColumn('metadata', 'json', ['null' => true]);
        $this->finish($cint); //uuid = project_id

        $lucid = $this->start('lucid')
            ->addColumn('surveyName', 'string')
            ->addColumn('countryLanguage', 'string')
            ->addColumn('industry', 'string')
            ->addColumn('studyType', 'string')
            ->addColumn('isLive', 'boolean')
            ->addColumn('collectsPII', 'boolean')
            ->addColumn('cpi', 'float')
            ->addColumn('lengthOfInterview', 'integer')
            ->addColumn('totalRemaining', 'integer')
            ->addColumn('overallCompletes', 'integer');
        $this->finish($lucid);
    }

    /**
     * @param string $tableName
     * @return Table
     */
    private function start(string $tableName): Table
    {
        return $this->setup->table($tableName, ['id' => false, 'primary_key' => ['uuid']])
            ->addColumn('uuid', 'string', ['limit' => 36])
            ->addIndex(['uuid'], ['unique' => true]);
    }

    /**
     * @param Table $table
     */
    private function finish(Table $table): void
    {
        $table->addColumn('createdAt', 'string', ['limit' => 25])
            ->addColumn('updatedAt', 'string', ['limit' => 25, 'null' => true])
            ->create();
    }
}
