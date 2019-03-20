<?php

use Movim\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreatePostsTable extends Migration
{
    public function up()
    {
        $this->schema->create('posts', function (Blueprint $table) {
            $table->increments('id');
            $table->string('server', 64);
            $table->string('node', 256);
            $table->string('nodeid', 192);
            $table->string('aname', 128)->nullable();
            $table->string('aid', 64)->nullable();
            $table->string('aemail', 64)->nullable();

            $table->text('title')->nullable();
            $table->text('content')->nullable();
            $table->text('contentraw')->nullable();
            $table->text('contentcleaned')->nullable();

            $table->string('commentserver', 64)->nullable();
            $table->string('commentnodeid', 192)->nullable();
            $table->integer('parent_id')->index()->nullable();

            $table->string('replyserver', 64)->nullable();
            $table->string('replynode', 256)->nullable();
            $table->string('replynodeid', 192)->nullable();

            $table->boolean('open')->default(false);
            $table->boolean('nsfw')->default(false);
            $table->boolean('like')->default(false);

            $table->datetime('published')->nullable();
            $table->datetime('updated')->nullable();
            $table->datetime('delay')->nullable();

            $table->timestamps();

            $table->unique(['server', 'node', 'nodeid']);
        });

        $this->schema->create('attachments', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('post_id')->unsigned();
            $table->string('category', 16);
            $table->string('rel', 16);
            $table->string('logo', 256)->nullable();
            $table->string('type', 32)->nullable();
            $table->string('href', 512);
            $table->text('title')->nullable();
            $table->text('description')->nullable();
            $table->timestamps();

            $table->foreign('post_id')
                  ->references('id')->on('posts')
                  ->onDelete('cascade');
        });
    }

    public function down()
    {
        $this->schema->drop('attachments');
        $this->schema->drop('posts');
    }
}
