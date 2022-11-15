<?php

use Movim\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddActivityAtColumnToPushSubscriptionsTable extends Migration
{
    public function up()
    {
        $this->schema->table('push_subscriptions', function (Blueprint $table) {
            $table->datetime('activity_at')->nullable();
        });
    }

    public function down()
    {
        $this->schema->table('push_subscriptions', function (Blueprint $table) {
            $table->dropColumn('activity_at');
        });
    }
}
