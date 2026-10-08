<?php

use Illuminate\Database\Schema\Blueprint;
use Movim\Migration;

class AddIndexParentmidToMessagesTable extends Migration
{
    public function up()
    {
        $this->schema->table('messages', function (Blueprint $table) {
            $table->index('parentmid');
        });
    }

    public function down()
    {
        $this->disableForeignKeyCheck();

        $this->schema->table('messages', function (Blueprint $table) {
            $table->dropIndex('messages_parentmid_index');
        });

        $this->enableForeignKeyCheck();
    }
}
