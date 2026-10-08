<?php

use Illuminate\Database\Schema\Blueprint;
use Movim\Migration;

class AddContentHashToPostsTable extends Migration
{
    public function up()
    {
        $this->schema->table('posts', function (Blueprint $table) {
            $table->string('contenthash', 64)->nullable();
        });
    }

    public function down()
    {
        $this->schema->table('posts', function (Blueprint $table) {
            $table->dropColumn('contenthash');
        });
    }
}
