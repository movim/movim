<?php

use Movim\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddJidtoAndTypeIndexesToMessages extends Migration
{
    public function up()
    {
        $this->schema->table('messages', function (Blueprint $table) {
            $table->index('jidto');
            $table->index('type');
        });
    }

    public function down()
    {
        $this->schema->table('messages', function (Blueprint $table) {
            $table->dropIndex('messages_jidto_index');
            $table->dropIndex('messages_type_index');
        });
    }
}
