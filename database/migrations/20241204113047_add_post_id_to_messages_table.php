<?php

use Movim\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddPostIdToMessagesTable extends Migration
{
    public function up()
    {
        $this->disableForeignKeyCheck();

        $this->schema->table('messages', function (Blueprint $table) {
            $table->integer('postid')->unsigned()->nullable();

            $table->foreign('postid')
                  ->references('id')->on('posts')
                  ->onDelete('set null');
        });

        $this->enableForeignKeyCheck();
    }

    public function down()
    {
        $this->disableForeignKeyCheck();

        $this->schema->table('messages', function (Blueprint $table) {
            $table->dropForeign('messages_postid_foreign');
            $table->dropColumn('postid');
        });

        $this->enableForeignKeyCheck();
    }
}
