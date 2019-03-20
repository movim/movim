<?php

use Movim\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateEncryptedPasswordsTable extends Migration
{
    public function up()
    {
        $this->schema->create('encrypted_passwords', function (Blueprint $table) {
            $table->string('user_id', 64);
            $table->string('id', 64);
            $table->text('data');
            $table->timestamps();

            $table->primary(['user_id', 'id']);

            $table->foreign('user_id')
                  ->references('id')->on('users')
                  ->onDelete('cascade');
        });
    }

    public function down()
    {
        $this->schema->drop('encrypted_passwords');
    }
}
