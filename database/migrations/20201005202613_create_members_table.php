<?php

use Movim\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateMembersTable extends Migration
{
    public function up()
    {
        $this->schema->create('members', function (Blueprint $table) {
            $table->increments('id');
            $table->string('conference', 128);
            $table->string('jid', 128);
            $table->string('nick', 128)->nullable();
            $table->string('role', 32)->nullable();
            $table->string('affiliation', 32);
            $table->timestamps();

            $table->unique(['conference', 'jid']);
            $table->index('conference');

            $table->foreign('jid')
                  ->references('id')->on('contacts');
        });
    }

    public function down()
    {
        $this->schema->drop('members');
    }
}
