<?php

use Illuminate\Database\Capsule\Manager as DB;
use Illuminate\Database\Schema\Blueprint;
use Movim\Migration;

class AddTimezoneToSessionsTable extends Migration
{
    public function up()
    {
        DB::table('sessions')->delete();

        $this->schema->table('sessions', function (Blueprint $table) {
            $table->string('timezone')->index();
        });
    }

    public function down()
    {
        $this->schema->table('sessions', function (Blueprint $table) {
            $table->dropColumn('timezone');
        });
    }
}
