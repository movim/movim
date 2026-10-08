<?php

use Movim\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateMAMEarliestTable extends Migration
{
    public function up()
    {
        $this->schema->create('mam_earliest', function (Blueprint $table) {
            $table->increments('id');

            $table->string('user_id');
            $table->string('to')->nullable();
            $table->string('jid')->nullable();
            $table->dateTime('earliest');

            $table->foreign('user_id')->references('id')
                  ->on('users')->onDelete('cascade');

            $table->timestamps();

            $table->unique(['user_id', 'to', 'jid']);
        });
    }

    public function down()
    {
        $this->schema->drop('mam_earliest');
    }
}
