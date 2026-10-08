<?php

use App\Post;
use Movim\Migration;
use Illuminate\Database\Schema\Blueprint;

class UpdateTitleUpdatedNotNullPostsTable extends Migration
{
    public function up()
    {
        Post::where('title', '')->delete();
        Post::whereNull('title')->delete();

        $this->schema->table('posts', function (Blueprint $table) {
            $table->text('title')->nullable(false)->change();
            $table->datetime('updated')->nullable(false)->change();
        });
    }

    public function down()
    {
        $this->schema->table('posts', function (Blueprint $table) {
            $table->text('title')->nullable(true)->change();
            $table->datetime('updated')->nullable(true)->change();
        });
    }
}
