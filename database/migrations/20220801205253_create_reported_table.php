<?php

use Movim\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateReportedTable extends Migration
{
    public function up()
    {
        $this->schema->create('reported', function (Blueprint $table) {
            $table->string('id', 256)->unique();
            $table->boolean('blocked')->default(false);
            $table->timestamps();
        });

        $this->schema->create('reported_user', function (Blueprint $table) {
            $table->string('user_id', 256);
            $table->string('reported_id', 256);

            $table->foreign('user_id')
                  ->references('id')->on('users')
                  ->onDelete('cascade');

            $table->foreign('reported_id')
                  ->references('id')->on('reported')
                  ->onDelete('cascade');

            $table->unique(['user_id', 'reported_id']);

            $table->timestamps();
        });
    }

    public function down()
    {
        $this->schema->drop('reported_user');
        $this->schema->drop('reported');
    }
}
