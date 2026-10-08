<?php

use Movim\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddParentThreadToMessagesTable extends Migration
{
    public function up()
    {
        $this->schema->table('messages', function (Blueprint $table) {
            $table->string('parentthread', 128)->nullable();
            $table->index('thread');
        });
    }

    public function down()
    {
        $this->schema->table('messages', function (Blueprint $table) {
            $table->dropColumn('parentthread');
            $table->dropIndex('messages_thread_index');
        });
    }
}
