<?php

use Movim\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddMucSemiAnonymousToInfosTable extends Migration
{
    public function up()
    {
        $this->schema->table('infos', function (Blueprint $table) {
            $table->boolean('mucsemianonymous')->default(false);
        });
    }

    public function down()
    {
        $this->schema->table('infos', function (Blueprint $table) {
            $table->dropColumn('mucsemianonymous');
        });
    }
}
