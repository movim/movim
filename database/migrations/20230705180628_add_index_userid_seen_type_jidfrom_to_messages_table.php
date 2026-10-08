<?php

use Movim\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddIndexUseridSeenTypeJidfromToMessagesTable extends Migration
{
    public function up()
    {
        $this->schema->table('messages', function (Blueprint $table) {
            $table->index(['user_id', 'seen', 'type', 'jidfrom']);
        });
    }

    public function down()
    {
        $this->schema->table('messages', function (Blueprint $table) {
            $table->dropIndex('messages_user_id_seen_type_jidfrom_index');
        });
    }
}
