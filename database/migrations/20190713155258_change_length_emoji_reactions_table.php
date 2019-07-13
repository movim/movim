<?php

use Movim\Migration;
use Illuminate\Database\Schema\Blueprint;

class ChangeLengthEmojiReactionsTable extends Migration
{
    public function up()
    {
        $this->schema->table('reactions', function (Blueprint $table) {
            $table->string('emoji', 32)->change();
        });
    }

    public function down()
    {
        $this->schema->table('reactions', function (Blueprint $table) {
            $table->string('emoji', 1)->change();
        });
    }
}