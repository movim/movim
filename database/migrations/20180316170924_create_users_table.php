<?php

use Illuminate\Database\Schema\Blueprint;
use Movim\Migration;

class CreateUsersTable extends Migration
{
    public function up()
    {
        $this->schema->create('users', function (Blueprint $table) {
            $table->string('id', 64);
            $table->text('configuration')->nullable();
            $table->string('language', 6)->nullable();
            $table->string('cssurl', 128)->nullable();
            $table->boolean('nightmode')->default(false);
            $table->boolean('nsfw')->default(false);
            $table->boolean('public')->nullable();
            $table->primary('id');
            $table->timestamps();
        });
    }

    public function down()
    {
        $this->schema->drop('users');
    }
}
