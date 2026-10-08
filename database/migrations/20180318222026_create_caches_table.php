<?php

use Movim\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateCachesTable extends Migration
{
    public function up()
    {
        $this->schema->create('caches', function (Blueprint $table) {
            $table->string('user_id', 64);
            $table->string('name', 64);
            $table->text('data');
            $table->timestamps();

            $table->primary(['user_id', 'name']);
            $table->foreign('user_id')
                  ->references('id')->on('users')
                  ->onDelete('cascade');
        });
    }

    public function down()
    {
        $this->schema->drop('caches');
    }
}
