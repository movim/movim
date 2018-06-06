<?php

use Movim\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddJidToIndexesToMessages extends Migration
{
    public function up()
    {
        $this->schema->table('messages', function(Blueprint $table) {
            $table->index('jidto');
        });
    }

    public function down()
    {
        $this->schema->table('messages', function(Blueprint $table) {
            $table->dropIndex('messages_jidto_index');
        });

    }
}
