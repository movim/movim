<?php

use App\PushSubscription;
use Movim\Migration;
use Illuminate\Database\Schema\Blueprint;

class UpdateCreatedAtNotNullInPushSubscriptionsTable extends Migration
{
    public function up()
    {
        PushSubscription::whereNull('created_at')->delete();

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
