<?php

use Illuminate\Database\Schema\Blueprint;
use Movim\Migration;

class RemoveJidForeignFromMembersTable extends Migration
{
    public function up()
    {
        $this->schema->table('members', function (Blueprint $table) {
            $table->dropForeign('members_jid_foreign');
        });

    }

    public function down()
    {
        $this->disableForeignKeyCheck();

        $this->schema->table('members', function (Blueprint $table) {
            $table->foreign('jid')
                ->references('id')->on('contacts');
        });

        $this->enableForeignKeyCheck();
    }
}
