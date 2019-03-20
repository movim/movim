<?php

use Movim\Migration;
use Illuminate\Database\Schema\Blueprint;

class UpdateUniqueConstraintSessions extends Migration
{
    public function up()
    {
        $this->schema->table('sessions', function (Blueprint $table) {
            $table->dropUnique('host');
            $table->unique('user_id');
        });
    }

    public function down()
    {
        $this->schema->table('sessions', function (Blueprint $table) {
            $table->dropUnique('sessions_user_id_unique');
            $table->unique('username', 'host');
        });
    }
}
