<?php

use Illuminate\Database\Schema\Blueprint;
use Movim\Migration;

class AddOriginIdToMessagesTable extends Migration
{
    public function up()
    {
        $this->schema->table('messages', function (Blueprint $table) {
            $table->string('originid')->nullable();
            $table->index('originid');
            $table->boolean('retracted')->default(false);
        });
    }

    public function down()
    {
        $this->schema->table('messages', function (Blueprint $table) {
            $table->dropIndex('messages_originid_index');
            $table->dropColumn('originid');
            $table->dropColumn('retracted');
        });
    }
}
