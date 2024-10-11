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
            $table->string('conference_id', 128);
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
            $table->dateTime('left_at')->nullable();

            $table->primary(['session_id', 'muji_call_id', 'jid']);

            $table->foreign('session_id')
                  ->references('id')->on('sessions')
                  ->onDelete('cascade');

            $table->timestamps();
        });
    }

    public function down()
    {
        $this->schema->drop('muji_call_participants');
        $this->schema->drop('muji_calls');
    }
}
