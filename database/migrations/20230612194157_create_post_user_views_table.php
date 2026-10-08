<?php

use Movim\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreatePostUserViewsTable extends Migration
{
    public function up()
    {
        $this->schema->create('post_user_views', function (Blueprint $table) {
            $table->string('user_id', 256);
            $table->integer('post_id')->unsigned();

            $table->foreign('user_id')
                  ->references('id')->on('users')
                  ->onDelete('cascade');

            $table->foreign('post_id')
                  ->references('id')->on('posts')
                  ->onDelete('cascade');

            $table->timestamps();

            $table->unique(['user_id', 'post_id']);
        });
    }

    public function down()
    {
        $this->schema->drop('post_user_views');
    }
}
