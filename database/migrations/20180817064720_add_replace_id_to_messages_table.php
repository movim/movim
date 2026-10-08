<?php

use Movim\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddReplaceIdToMessagesTable extends Migration
{
    public function up()
    {
        $this->schema->table('messages', function (Blueprint $table) {
            $table->string('replaceid', 64)->nullable();
            $table->index('replaceid');
        });
    }

    public function down()
    {
        $this->schema->table('messages', function (Blueprint $table) {
            $table->dropIndex('messages_replaceid_index');
            $table->dropColumn('replaceid');
        });
    }
}
