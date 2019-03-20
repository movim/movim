<?php

use Movim\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreatePresencesTable extends Migration
{
    public function up()
    {
        $this->schema->create('presences', function (Blueprint $table) {
            $table->string('session_id', 32);
            $table->string('jid', 64);
            $table->string('resource', 128)->nullable();
            $table->integer('value');
            $table->integer('priority');
            $table->string('status')->nullable();
            $table->string('node')->nullable();
            $table->dateTime('delay')->nullable();
            $table->integer('last')->nullable();
            $table->boolean('muc');
            $table->string('mucjid')->nullable();
            $table->string('mucaffiliation', 32)->nullable();
            $table->string('mucrole', 32)->nullable();
            $table->timestamps();

            $table->primary(['session_id', 'jid', 'resource']);
            $table->foreign('session_id')
                  ->references('id')->on('sessions')
                  ->onDelete('cascade');
        });
    }

    public function down()
    {
        $this->schema->drop('presences');
    }
}
