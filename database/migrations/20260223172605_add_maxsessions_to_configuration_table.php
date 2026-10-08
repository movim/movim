<?php

use Illuminate\Database\Schema\Blueprint;
use Movim\Migration;

class AddMaxsessionsToConfigurationTable extends Migration
{
    public function up()
    {
        $this->schema->table('configuration', function (Blueprint $table) {
            $table->integer('maxsessions')->default(0);
        });
    }

    public function down()
    {
        $this->schema->table('configuration', function (Blueprint $table) {
            $table->dropColumn('maxsessions');
        });
    }
}
