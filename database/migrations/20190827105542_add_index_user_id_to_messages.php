<?php

use Movim\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddIndexUserIdToMessages extends Migration
{
    public function up()
    {
        $this->schema->table('messages', function (Blueprint $table) {
            $table->index('user_id');
        });
    }

    public function down()
    {
        $this->schema->table('messages', function (Blueprint $table) {
            $table->dropIndex('messages_user_id_index');
        });
    }
}
