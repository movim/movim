<?php

use Illuminate\Database\Schema\Blueprint;
use Movim\Migration;

class AddOmemoHeaderToMessagesTable extends Migration
{
    public function up()
    {
        $this->schema->table('messages', function (Blueprint $table) {
            $table->text('omemoheader')->nullable();
        });
    }

    public function down()
    {
        $this->schema->table('messages', function (Blueprint $table) {
            $table->dropColumn('omemoheader');
        });
    }
}
