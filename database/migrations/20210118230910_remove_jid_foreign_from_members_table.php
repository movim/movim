<?php

use Movim\Migration;
use Illuminate\Database\Schema\Blueprint;

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
        $this->schema->table('members', function (Blueprint $table) {
            $table->foreign('jid')
                  ->references('id')->on('contacts');
        });

    }
}