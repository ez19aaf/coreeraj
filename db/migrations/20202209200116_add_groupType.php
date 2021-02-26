<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;
use Survey54\Library\Domain\Values\GroupType;

class AddGroupType extends AbstractMigration
{
    public function change(): void
    {
        $table = $this->table('group');
        $table->addColumn('groupType', 'enum', ['values' => GroupType::toArray()])
                ->update();
    }
}
