<?php

use Movim\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateReactionsTable extends Migration
{
    public function up()
    {
        $this->disableForeignKeyCheck();

        $this->schema->table('messages', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropPrimary('messages_pkey');
            $table->foreign('user_id')
                ->references('id')->on('users')
                ->onDelete('cascade');
        });

        $this->schema->table('messages', function (Blueprint $table) {
            $table->increments('mid');
            $table->unique(['user_id', 'jidfrom', 'id']);
        });

        $this->schema->create('reactions', function (Blueprint $table) {
            $table->integer('message_mid')->unsigned();
            $table->string('jidfrom', 256);
            $table->string('emoji', 1);
            $table->timestamps();

            $table->foreign('message_mid')->references('mid')
                  ->on('messages')->onDelete('cascade');

            $table->unique(['message_mid', 'jidfrom', 'emoji']);
        });

        $this->enableForeignKeyCheck();
    }

    public function down()
    {
        $this->disableForeignKeyCheck();

        $this->schema->drop('reactions');

        $this->schema->table('messages', function (Blueprint $table) {
            if ($this->schema->getConnection()->getDriverName() == 'pgsql') {
                $table->dropPrimary('messages_pkey');
            }

            $table->dropColumn('mid');
            $table->primary(['user_id', 'jidfrom', 'id']);
            $table->dropUnique('messages_user_id_jidfrom_id_unique');
        });

        $this->enableForeignKeyCheck();
    }
}
