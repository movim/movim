<?php

use Illuminate\Database\Schema\Blueprint;
use Movim\Migration;

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
