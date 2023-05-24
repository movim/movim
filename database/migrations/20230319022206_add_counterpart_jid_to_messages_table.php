<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AddCounterpartJidToMessagesTable extends AbstractMigration
{
    public function change(): void
    {
        $table = $this->table('messages');
        $table->addColumn('counterpartjid', 'string')->update();
    }
}
