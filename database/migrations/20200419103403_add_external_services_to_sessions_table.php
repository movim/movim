<?php

use Illuminate\Database\Schema\Blueprint;
use Movim\Migration;

class AddExternalServicesToSessionsTable extends Migration
{
    public function up()
    {
        $this->schema->table('sessions', function (Blueprint $table) {
            $table->text('externalservices')->nullable();
        });
    }

    public function down()
    {
        $this->schema->table('sessions', function (Blueprint $table) {
            $table->dropColumn('externalservices');
        });
    }
}
