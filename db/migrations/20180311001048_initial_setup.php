<?php

use Phinx\Migration\AbstractMigration;

require __DIR__ . '/CoreTables.php';
require __DIR__ . '/PlugTables.php';
require __DIR__ . '/ReapTables.php';

class InitialSetup extends AbstractMigration
{
    /**
     * Change Method.
     * https://book.cakephp.org/phinx/0/en/migrations.html
     */
    public function change(): void
    {
        (new CoreTables())->setSetup($this)->create();
        (new PlugTables())->setSetup($this)->create();
        (new ReapTables())->setSetup($this)->create();
    }
}
