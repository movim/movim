<?php

use Movim\Migration;
use Illuminate\Database\Schema\Blueprint;

use App\Message;

class AddSeenToMessagesTable extends Migration
{
    public function up()
    {
        $this->schema->table('messages', function (Blueprint $table) {
            $table->boolean('seen')->default(true);
        });

        $this->schema->table('messages', function (Blueprint $table) {
            $table->boolean('seen')->default(false)->change();
        });
    }

    public function down()
    {
        $this->schema->table('messages', function (Blueprint $table) {
            $table->dropColumn('seen');
        });
    }
}
