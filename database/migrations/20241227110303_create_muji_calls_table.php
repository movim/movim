<?php

use Movim\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateMujiCallsTable extends Migration
{
    public function up()
    {
        $this->schema->create('muji_calls', function (Blueprint $table) {
            $table->string('id', 256);
            $table->string('muc', 256);
            $table->string('session_id', 64);
            $table->string('jidfrom', 128);
            $table->boolean('isfromconference')->default(false);
            $table->boolean('video', false);

            $table->foreign('session_id')
                  ->references('id')->on('sessions')
                  ->onDelete('cascade');

            $table->primary(['id', 'session_id']);

            $table->timestamps();
        });

        $this->schema->create('muji_call_participants', function (Blueprint $table) {
            $table->string('session_id', 64);
            $table->string('muji_call_id', 256);
            $table->string('jid', 256);
            $table->boolean('inviter')->default(false);
            $table->dateTime('left_at')->nullable();

            $table->primary(['session_id', 'muji_call_id', 'jid']);

            $table->foreign('session_id')
                  ->references('id')->on('sessions')
                  ->onDelete('cascade');

            $table->foreign(['session_id', 'muji_call_id'])
                  ->references(['session_id', 'id'])->on('muji_calls')
                  ->onDelete('cascade');

            $table->timestamps();
        });

        $this->schema->table('presences', function (Blueprint $table) {
            $table->string('mucjidresource')->nullable();
        });
    }

    public function down()
    {
        $this->schema->drop('muji_call_participants');
        $this->schema->drop('muji_calls');

        $this->schema->table('presences', function (Blueprint $table) {
            $table->dropForeign('mucjidresource');
        });
    }
}
