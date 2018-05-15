<?php

use Movim\Migration;
use Illuminate\Database\Schema\Blueprint;

class ChangeMessagesPrimaryKey extends Migration
{
    public function up()
    {
        $this->schema->table('messages', function(Blueprint $table) {
            $table->dropPrimary('messages_pkey');
            $table->primary(['user_id', 'jidfrom', 'id']);
        });
    }

    public function down()
    {
        $this->schema->table('messages', function(Blueprint $table) {
            $table->dropPrimary('messages_pkey');
            $table->primary(['user_id', 'id']);
        });
    }
}
