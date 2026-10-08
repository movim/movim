<?php

use Movim\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateDraftsTable extends Migration
{
    public function up()
    {
        $this->disableForeignKeyCheck();

        $this->schema->create('drafts', function (Blueprint $table) {
            $table->increments('id');
            $table->string('user_id', 256);
            $table->string('server', 64);
            $table->string('node', 256);
            $table->string('nodeid', 192);
            $table->integer('reply_id')->unsigned()->nullable();
            $table->boolean('open')->default(false);
            $table->text('title')->nullable();
            $table->text('content')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'server', 'node', 'nodeid']);

            $table->foreign('user_id')
                  ->references('id')->on('users')
                  ->onDelete('cascade');

            $table->foreign('reply_id')
                  ->references('id')->on('posts')
                  ->onDelete('cascade');
        });

        $this->schema->create('embeds', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('draft_id')->unsigned();
            $table->string('url', 384);
            $table->integer('imagenumber')->default(0);
            $table->timestamps();

            $table->foreign('draft_id')
                  ->references('id')->on('drafts')
                  ->onDelete('cascade');

            $table->unique(['draft_id', 'url']);
        });

        $this->enableForeignKeyCheck();
    }

    public function down()
    {
        $this->disableForeignKeyCheck();

        $this->schema->drop('embeds');
        $this->schema->drop('drafts');

        $this->enableForeignKeyCheck();
    }
}
