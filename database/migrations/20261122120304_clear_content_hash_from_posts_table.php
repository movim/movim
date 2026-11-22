<?php

use App\Post;
use Illuminate\Database\Schema\Blueprint;
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
