<?php

use Illuminate\Database\Schema\Blueprint;
use Movim\Migration;

class AddIndexAidToPostsTable extends Migration
{
    public function up()
    {
        $this->schema->table('posts', function (Blueprint $table) {
            $table->index('aid');
        });
    }

    public function down()
    {
        $this->schema->table('posts', function (Blueprint $table) {
            $table->dropIndex('posts_aid_index');
        });
    }
}
