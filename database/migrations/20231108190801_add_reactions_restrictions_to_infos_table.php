<?php

use Illuminate\Database\Schema\Blueprint;
use Movim\Migration;

class AddReactionsRestrictionsToInfosTable extends Migration
{
    public function up()
    {
        $this->schema->table('infos', function (Blueprint $table) {
            $table->text('reactionsrestrictions')->nullable();
        });
    }

    public function down()
    {
        $this->schema->table('infos', function (Blueprint $table) {
            $table->dropColumn('reactionsrestrictions');
        });
    }
}
