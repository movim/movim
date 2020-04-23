<?php

use Movim\Migration;
use Illuminate\Database\Schema\Blueprint;

class RemoveExternalServicesToSessionsTable extends Migration
{
    public function up()
    {
        $this->schema->table('sessions', function (Blueprint $table) {
            $table->dropColumn('externalservices');
        });
    }

    public function down()
    {
        $this->schema->table('sessions', function (Blueprint $table) {
            $table->text('externalservices')->nullable();
        });
    }
}
