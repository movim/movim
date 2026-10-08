<?php

use Movim\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddTypeToInfosTable extends Migration
{
    public function up()
    {
        $this->schema->table('infos', function (Blueprint $table) {
            $table->string('type', 256)->nullable()->index();
        });
    }

    public function down()
    {
        $this->schema->table('infos', function (Blueprint $table) {
            $table->dropColumn('type');
        });
    }
}
