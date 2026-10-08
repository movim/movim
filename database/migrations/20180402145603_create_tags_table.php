<?php

use Movim\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateTagsTable extends Migration
{
    public function up()
    {
        $this->schema->create('tags', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name', 64);

            $table->timestamps();

            $table->unique('name');
        });

        $this->schema->create('post_tag', function (Blueprint $table) {
            $table->integer('post_id')->unsigned();
            $table->integer('tag_id')->unsigned();

            $table->foreign('post_id')->references('id')
                  ->on('posts')->onDelete('cascade');
            $table->foreign('tag_id')->references('id')
                  ->on('tags')->onDelete('cascade');

            $table->timestamps();

            $table->unique(['post_id', 'tag_id']);
        });
    }

    public function down()
    {
        $this->schema->drop('post_tag');
        $this->schema->drop('tags');
    }
}
