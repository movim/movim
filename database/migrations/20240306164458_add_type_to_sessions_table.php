<?php

use Illuminate\Database\Schema\Blueprint;
use Movim\Migration;

class AddTypeToSessionsTable extends Migration
{
    public function up()
    {
        $this->schema->table('sessions', function (Blueprint $table) {
            $table->string('type')->nullable();
        });
    }

    public function down()
    {
        $this->schema->table('sessions', function (Blueprint $table) {
            $table->dropColumn('type');
        });
    }
}
