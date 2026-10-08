<?php

use App\Subscription;
use Illuminate\Database\Schema\Blueprint;
use Movim\Migration;

class AddSpaceToSubscriptionsTable extends Migration
{
    public function up()
    {
        $this->schema->table('subscriptions', function (Blueprint $table) {
            $table->boolean('space')->default(false);
            $table->boolean('space_in')->default(false);
        });
    }

    public function down()
    {
        Subscription::where('space', true)->delete();
        $this->schema->table('subscriptions', function (Blueprint $table) {
            $table->dropColumn('space');
            $table->dropColumn('space_in');
        });
    }
}
