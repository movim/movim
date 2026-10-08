<?php

use Movim\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddChatMainToUsersTable extends Migration
{
    public function up()
    {
        $this->schema->table('users', function (Blueprint $table) {
            $table->boolean('chatmain')->default(false);
        });
    }

    public function down()
    {
        $this->schema->table('users', function (Blueprint $table) {
            $table->dropColumn('chatmain');
        });
    }
}
