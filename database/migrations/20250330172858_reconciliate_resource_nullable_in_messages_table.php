<?php

use App\Message;
use Illuminate\Database\Schema\Blueprint;
use Movim\Migration;

/**
 * Required after the behavior change introduced in
 * https://laravel.com/docs/11.x/upgrade#modifying-columns
 */
class ReconciliateResourceNullableInMessagesTable extends Migration
{
    public function up()
    {
        $this->schema->table('messages', function (Blueprint $table) {
            $table->string('resource', 256)->nullable()->change();
        });
    }

    public function down()
    {
        Message::whereNull('resource')->delete();

        $this->schema->table('messages', function (Blueprint $table) {
            $table->string('resource', 256)->nullable(false)->change();
        });
    }
}
