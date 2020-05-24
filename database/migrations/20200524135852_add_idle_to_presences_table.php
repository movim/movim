<?php

use Movim\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddIdleToPresencesTable extends Migration
{
    public function up()
    {
        $this->schema->table('presences', function (Blueprint $table) {
            $table->datetime('idle')->nullable();
        });
    }

    public function down()
    {
        $this->schema->table('presences', function (Blueprint $table) {
            $table->dropColumn('idle');
        });
    }
}
