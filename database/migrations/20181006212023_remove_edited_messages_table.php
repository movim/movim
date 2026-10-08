<?php

use Movim\Migration;
use Illuminate\Database\Schema\Blueprint;

class RemoveEditedMessagesTable extends Migration
{
    public function up()
    {
        $this->schema->table('messages', function (Blueprint $table) {
            $table->dropColumn('edited');
        });
    }

    public function down()
    {
        $this->schema->table('messages', function (Blueprint $table) {
            $table->boolean('edited')->default(false);
        });
    }
}
