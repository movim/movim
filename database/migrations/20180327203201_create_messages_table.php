<?php

use Movim\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateMessagesTable extends Migration
{
    public function up()
    {
        $this->schema->create('messages', function (Blueprint $table) {
            $table->string('user_id', 64);
            $table->string('id', 64);
            $table->string('oldid', 64)->nullable();
            $table->string('jidto', 96);
            $table->string('jidfrom', 96);
            $table->string('resource', 96)->nullable();
            $table->string('type', 16);
            $table->text('subject')->nullable();
            $table->string('thread', 128)->nullable();
            $table->text('body')->nullable();
            $table->text('html')->nullable();
            $table->datetime('published');
            $table->datetime('delivered')->nullable();
            $table->datetime('displayed')->nullable();
            $table->boolean('quoted')->default(false);
            $table->boolean('markable')->default(false);
            $table->boolean('edited')->default(false);
            $table->text('picture')->nullable();
            $table->string('sticker', 128)->nullable();
            $table->text('file')->nullable();
            $table->timestamps();

            $table->primary(['user_id', 'id']);
            $table->foreign('user_id')
                  ->references('id')->on('users')
                  ->onDelete('cascade');
        });
    }

    public function down()
    {
        $this->schema->drop('messages');
    }
}
