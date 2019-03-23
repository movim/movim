<?php

use Movim\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateReactionsTable extends Migration
{
    public function up()
    {
        $this->schema->table('messages', function (Blueprint $table) {
            $table->dropPrimary('messages_pkey');
        });

        $this->schema->table('messages', function (Blueprint $table) {
            $table->increments('mid');
            $table->unique(['user_id', 'jidfrom', 'id']);
        });

        $this->schema->create('reactions', function (Blueprint $table) {
            $table->integer('message_mid');
            $table->foreign('message_mid')->references('mid')
                  ->on('messages')->onDelete('cascade');
            $table->string('jidfrom', 256);
            $table->string('emoji', 1);

            $table->timestamps();

            $table->unique(['message_mid', 'jidfrom', 'emoji']);
        });
    }

    public function down()
    {
        $this->schema->drop('reactions');

        $this->schema->table('messages', function (Blueprint $table) {
            $table->dropUnique('messages_user_id_jidfrom_id_unique');
            $table->dropPrimary('messages_pkey');
            $table->dropColumn('mid');
            $table->primary(['user_id', 'jidfrom', 'id']);
        });
    }
}
