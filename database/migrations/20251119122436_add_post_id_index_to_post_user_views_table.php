<?php

use Movim\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddPostIdIndexToPostUserViewsTable extends Migration
{
    public function up()
    {
        $this->schema->table('post_user_views', function (Blueprint $table) {
            $table->index('post_id');
        });
    }

    public function down()
    {
        $this->schema->table('post_user_views', function (Blueprint $table) {
            $table->dropIndex('post_user_views_post_id_index');
        });
    }
}
