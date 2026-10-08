<?php

use Illuminate\Database\Schema\Blueprint;
use Movim\Migration;

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
