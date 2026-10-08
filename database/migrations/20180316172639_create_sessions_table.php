<?php

use Movim\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateSessionsTable extends Migration
{
    public function up()
    {
        $this->schema->create('sessions', function (Blueprint $table) {
            $table->string('id', 32);
            $table->string('user_id', 64);
            $table->string('username', 64);
            $table->string('host', 64);
            $table->string('hash');
            $table->string('resource', 64);
            $table->boolean('active');
            $table->timestamps();

            $table->primary('id');
            $table->unique('username', 'host');
            $table->foreign('user_id')
                  ->references('id')->on('users');
        });
    }

    public function down()
    {
        $this->schema->drop('sessions');
    }
}
