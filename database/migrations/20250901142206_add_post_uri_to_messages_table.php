<?php

use Illuminate\Database\Schema\Blueprint;
use Movim\Migration;

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
