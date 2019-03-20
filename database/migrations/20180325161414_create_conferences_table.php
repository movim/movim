<?php

use Movim\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateConferencesTable extends Migration
{
    public function up()
    {
        $this->schema->create('conferences', function (Blueprint $table) {
            $table->string('session_id', 64);
            $table->string('conference', 128);
            $table->string('name', 128);
            $table->string('nick', 128)->nullable();
            $table->boolean('autojoin')->default(false);
            $table->timestamps();

            $table->primary(['session_id', 'conference']);

            $table->foreign('session_id')
                  ->references('id')->on('sessions')
                  ->onDelete('cascade');
        });
    }

    public function down()
    {
        $this->schema->drop('conferences');
    }
}
