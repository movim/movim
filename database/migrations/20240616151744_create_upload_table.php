<?php

use Movim\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateUploadTable extends Migration
{
    public function up()
    {
        $this->schema->create('upload', function (Blueprint $table) {
            $table->string('id')->unique();

            $table->string('user_id', 256);
            $table->foreign('user_id')
                  ->references('id')->on('users')
                  ->onDelete('cascade');

            $table->string('jidto')->index();
            $table->string('name');
            $table->integer('size');
            $table->string('type');

            $table->string('puturl')->nullable();
            $table->string('geturl')->nullable();
            $table->text('headers')->nullable();

            $table->boolean('uploaded')->default(false);

            $table->timestamps();
        });
    }

    public function down()
    {
        $this->schema->drop('upload');
    }
}
