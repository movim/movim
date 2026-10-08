<?php

use App\Conference;
use App\Subscription;
use Movim\Migration;
use Illuminate\Database\Schema\Blueprint;

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
