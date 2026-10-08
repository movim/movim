<?php

use Illuminate\Database\Schema\Blueprint;
use Movim\Migration;

class AddTypeToPresencesTable extends Migration
{
    public function up()
    {
        $this->schema->table('presences', function (Blueprint $table) {
            $table->string('type', 12)->nullable()->index();
        });
    }

    public function down()
    {
        $this->schema->table('presences', function (Blueprint $table) {
            $table->dropColumn('type');
        });
    }
}
