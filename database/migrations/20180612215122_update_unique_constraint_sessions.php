<?php

use Movim\Migration;
use Illuminate\Database\Schema\Blueprint;

class UpdateUniqueConstraintSessions extends Migration
{
    public function up()
    {
        $this->disableForeignKeyCheck();

        $this->schema->table('sessions', function (Blueprint $table) {
            $table->dropUnique('host');
            $table->unique('user_id');
        });

        $this->enableForeignKeyCheck();
    }

    public function down()
    {
        $this->disableForeignKeyCheck();

        $this->schema->table('sessions', function (Blueprint $table) {
            $table->dropUnique('sessions_user_id_unique');
            $table->unique('username', 'host');
        });

        $this->enableForeignKeyCheck();
    }
}
