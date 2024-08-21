<?php

use Movim\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddCascadeOnUsersSessionsForeignKey extends Migration
{
    public function up()
    {
        $this->disableForeignKeyCheck();

        $this->schema->table('sessions', function (Blueprint $table) {
            $table->dropForeign('sessions_user_id_foreign');

            $table->foreign('user_id')
                ->references('id')->on('users')
                ->onDelete('cascade');
        });

        $this->enableForeignKeyCheck();
    }

    public function down()
    {
        $this->disableForeignKeyCheck();

        $this->schema->table('sessions', function (Blueprint $table) {
            $table->dropForeign('sessions_user_id_foreign');

            $table->foreign('user_id')
                ->references('id')->on('users');
        });

        $this->enableForeignKeyCheck();
    }
}
