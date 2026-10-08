<?php

use Illuminate\Database\Schema\Blueprint;
use Movim\Migration;

class AddResolvedToMessagesTable extends Migration
{
    public function up()
    {
        $this->schema->table('messages', function (Blueprint $table) {
            $table->boolean('resolved')->default(false);
        });
    }

    public function down()
    {
        $this->schema->table('messages', function (Blueprint $table) {
            $table->dropColumn('resolved');
        });
    }
}
