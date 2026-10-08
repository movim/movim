<?php

use Movim\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddEnabledAndBrowserVersionToPushSubscriptionsTable extends Migration
{
    public function up()
    {
        $this->schema->table('push_subscriptions', function (Blueprint $table) {
            $table->boolean('enabled')->default(true);
            $table->string('browser_version')->nullable();
        });
    }

    public function down()
    {
        $this->schema->table('push_subscriptions', function (Blueprint $table) {
            $table->dropColumn('enabled');
            $table->dropColumn('browser_version');
        });
    }
}
