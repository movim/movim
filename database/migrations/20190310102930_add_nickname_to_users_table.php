<?php

use Movim\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddNicknameToUsersTable extends Migration
{
    public function up()
    {
        $this->schema->table('users', function(Blueprint $table) {
            $table->string('nickname', 64)->nullable();
            $table->unique('nickname');
        });
    }

    public function down()
    {
        $this->schema->table('users', function(Blueprint $table) {
            $table->dropUnique('users_nickname_unique');
            $table->dropColumn('nickname');
        });
    }
}
