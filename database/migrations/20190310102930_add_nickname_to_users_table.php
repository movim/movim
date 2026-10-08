<?php

use Movim\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddNicknameToUsersTable extends Migration
{
    public function up()
    {
        $this->disableForeignKeyCheck();

        $this->schema->table('users', function (Blueprint $table) {
            $table->string('nickname', 64)->nullable();
            $table->unique('nickname');
        });

        $this->enableForeignKeyCheck();
    }

    public function down()
    {
        $this->disableForeignKeyCheck();

        $this->schema->table('users', function (Blueprint $table) {
            $table->dropUnique('users_nickname_unique');
            $table->dropColumn('nickname');
        });

        $this->enableForeignKeyCheck();
    }
}
