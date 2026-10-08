<?php

use Movim\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddPostUriToMessagesTable extends Migration
{
    public function up()
    {
        $this->schema->table('messages', function (Blueprint $table) {
            $table->longText('posturi')->nullable();
        });
    }

    public function down()
    {
        $this->schema->table('messages', function (Blueprint $table) {
            $table->dropColumn('posturi');
        });
    }
}
