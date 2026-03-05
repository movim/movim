<?php

use Movim\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddNotifyPinnedAndExtensionsToSubscriptionsTable extends Migration
{
    public function up()
    {

        $this->schema->table('subscriptions', function (Blueprint $table) {
            $table->text('extensions')->nullable();
            $table->boolean('pinned')->default(false);
            $table->integer('notify')->nullable();
        });
    }

    public function down()
    {
        $this->schema->table('subscriptions', function (Blueprint $table) {
            $table->dropColumn('extensions');
            $table->dropColumn('pinned');
            $table->dropColumn('notify');
        });
    }
}
