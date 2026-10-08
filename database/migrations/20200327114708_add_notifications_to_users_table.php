<?php

use Movim\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddNotificationsToUsersTable extends Migration
{
    public function up()
    {
        $this->schema->table('users', function (Blueprint $table) {
            $table->boolean('notificationchat')->default(true);
            $table->boolean('notificationcall')->default(true);
        });
    }

    public function down()
    {
        $this->schema->table('users', function (Blueprint $table) {
            $table->dropColumn('notificationchat');
            $table->dropColumn('notificationcall');
        });
    }
}
