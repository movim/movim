<?php

use Movim\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddSessionIdIndexPresences extends Migration
{
    public function up()
    {
        $this->schema->table('presences', function (Blueprint $table) {
            $table->index('session_id');
        });
    }

    public function down()
    {
        $this->disableForeignKeyCheck();

        $this->schema->table('presences', function (Blueprint $table) {
            $table->dropIndex('presences_session_id_index');
        });

        $this->enableForeignKeyCheck();
    }
}
