<?php

use Movim\Migration;
use Illuminate\Database\Schema\Blueprint;

class ChangeMessagesPrimaryKey extends Migration
{
    public function up()
    {
        $this->schema->table('messages', function (Blueprint $table) {
            if ($this->schema->getConnection()->getDriverName() !== 'sqlite') {
                $table->dropForeign(['user_id']);
            }

            $table->dropPrimary('messages_pkey');
            $table->primary(['user_id', 'jidfrom', 'id']);
            $table->foreign('user_id')
                  ->references('id')->on('users')
                  ->onDelete('cascade');
        });
    }

    public function down()
    {
        $this->schema->table('messages', function (Blueprint $table) {
            if ($this->schema->getConnection()->getDriverName() !== 'sqlite') {
                $table->dropForeign(['user_id']);
            }

            $table->dropPrimary('messages_pkey');
            $table->primary(['user_id', 'id']);
            $table->foreign('user_id')
                  ->references('id')->on('users')
                  ->onDelete('cascade');
        });
    }
}
