<?php

use Movim\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddJidToJidFromIndexesToMessages extends Migration
{
    public function up()
    {
        $this->schema->table('messages', function(Blueprint $table) {
            $table->index(['user_id', 'jidfrom', 'jidto', 'id'])->nullable();
        });
    }

    public function down()
    {
        $this->schema->table('messages', function(Blueprint $table) {
            $table->dropIndex('messages_user_id_jidfrom_jidto_id_index');
        });

    }
}
