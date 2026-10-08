<?php

use Illuminate\Database\Schema\Blueprint;
use Movim\Migration;

class AddSyncedToReportedUserTable extends Migration
{
    public function up()
    {
        $this->schema->table('reported_user', function (Blueprint $table) {
            $table->boolean('synced')->default(false);
        });
    }

    public function down()
    {
        $this->schema->table('reported_user', function (Blueprint $table) {
            $table->dropColumn('synced');
        });
    }
}
