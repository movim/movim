<?php

use Movim\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddOriginIdIndexToMessagesTable extends Migration
{
    public function up()
    {
        $this->schema->table('messages', function (Blueprint $table) {
            $table->index('originid');
        });
    }

    public function down()
    {
        $this->schema->table('messages', function (Blueprint $table) {
            $table->dropIndex('messages_originid_index');
        });
    }
}
