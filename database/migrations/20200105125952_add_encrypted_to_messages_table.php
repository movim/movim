<?php

use Illuminate\Database\Schema\Blueprint;
use Movim\Migration;

class AddEncryptedToMessagesTable extends Migration
{
    public function up()
    {
        $this->schema->table('messages', function (Blueprint $table) {
            $table->boolean('encrypted')->default(false);
        });
    }

    public function down()
    {
        $this->schema->table('messages', function (Blueprint $table) {
            $table->dropColumn('encrypted');
        });
    }
}
