<?php

use Movim\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddIdIndexToMessages extends Migration
{
    public function up()
    {
        $this->schema->table('messages', function (Blueprint $table) {
            $table->index('id');
        });
    }

    public function down()
    {
        $this->schema->table('messages', function (Blueprint $table) {
            $table->dropIndex('messages_id_index');
        });
    }
}
