<?php

use Movim\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Capsule\Manager as DB;

class AddMucjidPresencesPrimaryKey extends Migration
{
    public function up()
    {
        $this->disableForeignKeyCheck();

        DB::table('presences')->delete();

        $this->schema->table('presences', function (Blueprint $table) {
            $table->dropPrimary(['session_id', 'jid', 'resource']);
            $table->primary(['session_id', 'jid', 'mucjid', 'resource']);
        });

        $this->enableForeignKeyCheck();
    }

    public function down()
    {
        $this->disableForeignKeyCheck();

        $this->schema->table('presences', function (Blueprint $table) {
            $table->dropPrimary(['session_id', 'jid', 'mucjid', 'resource']);
            $table->primary(['session_id', 'jid', 'resource']);
        });

        $this->enableForeignKeyCheck();
    }
}
