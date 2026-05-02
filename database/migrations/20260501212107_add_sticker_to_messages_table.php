<?php

use Movim\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddStickerToMessagesTable extends Migration
{
    public function up()
    {
        $this->schema->table('messages', function (Blueprint $table) {
            $table->boolean('sticker')->default(false);
        });
    }

    public function down()
    {
       $this->schema->table('messages', function (Blueprint $table) {
            $table->dropColumn('sticker');
        });
    }
}
