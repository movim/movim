<?php

use Illuminate\Database\Schema\Blueprint;
use Movim\Migration;

class AddInlinesToMessagesTable extends Migration
{
    public function up()
    {
        $this->schema->table('messages', function (Blueprint $table) {
            $table->text('inlines')->nullable();
        });
    }

    public function down()
    {
        $this->schema->table('messages', function (Blueprint $table) {
            $table->dropColumn('inlines');
        });
    }
}
