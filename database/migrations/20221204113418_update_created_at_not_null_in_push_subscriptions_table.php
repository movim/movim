<?php

use App\PushSubscription;
use Illuminate\Database\Schema\Blueprint;
use Movim\Migration;

class UpdateCreatedAtNotNullInPushSubscriptionsTable extends Migration
{
    public function up()
    {
        PushSubscription::whereNull('activity_at')->delete();

        $this->schema->table('push_subscriptions', function (Blueprint $table) {
            $table->datetime('activity_at')->nullable(false)->change();
        });
    }

    public function down()
    {
        $this->schema->table('push_subscriptions', function (Blueprint $table) {
            $table->datetime('activity_at')->nullable()->change();
        });
    }
}
