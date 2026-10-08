<?php

use Movim\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddIndexesPresencesPostsMessagesTables extends Migration
{
    public function up()
    {
        $this->schema->table('presences', function (Blueprint $table) {
            $table->index('jid');
            $table->index('resource');
        });

        $this->schema->table('messages', function (Blueprint $table) {
            $table->index('jidfrom');
            $table->index('published');
        });

        $this->schema->table('posts', function (Blueprint $table) {
            $table->index('server');
            $table->index('node');
            $table->index('published');
        });
    }

    public function down()
    {
        $this->schema->table('presences', function (Blueprint $table) {
            $table->dropIndex('presences_jid_index');
            $table->dropIndex('presences_resource_index');
        });

        $this->schema->table('messages', function (Blueprint $table) {
            $table->dropIndex('messages_jidfrom_index');
            $table->dropIndex('messages_published_index');
        });

        $this->schema->table('posts', function (Blueprint $table) {
            $table->dropIndex('posts_server_index');
            $table->dropIndex('posts_node_index');
            $table->dropIndex('posts_published_index');
        });
    }
}
