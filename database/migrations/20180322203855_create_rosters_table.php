<?php

use Movim\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateRostersTable extends Migration
{
    public function up()
    {
        $this->schema->create('rosters', function (Blueprint $table) {
            $table->string('session_id', 64);
            $table->string('jid', 64);
            $table->string('name', 128)->nullable();
            $table->string('ask', 16)->nullable();
            $table->string('subscription', 4)->nullable();
            $table->string('group', 256)->nullable();
            $table->timestamps();

            $table->primary(['session_id', 'jid']);

            $table->foreign('session_id')
                  ->references('id')->on('sessions')
                  ->onDelete('cascade');
        });
    }

    public function down()
    {
        $this->schema->drop('rosters');
    }
}
