<?php

use App\Post;
use Movim\Migration;

class ClearContentHashFromPostsTable extends Migration
{
    public function up()
    {
        Post::query()->update(['contenthash' => null]);
    }

    public function down()
    {
    }
}
