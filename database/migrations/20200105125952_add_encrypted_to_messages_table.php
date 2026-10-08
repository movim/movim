<?php

use Movim\Migration;
use Illuminate\Database\Schema\Blueprint;

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
