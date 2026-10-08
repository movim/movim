<?php

use Movim\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateInvitesTable extends Migration
{
    public function up()
    {
        $this->schema->create('invites', function (Blueprint $table) {
            $table->string('code', 8);
            $table->string('user_id', 64);
            $table->string('resource', 128);
            $table->timestamps();

            $table->primary('code');
            $table->foreign('user_id')
                  ->references('id')->on('users')
                  ->onDelete('cascade');
        });
    }

    public function down()
    {
        $this->schema->drop('invites');
    }
}
