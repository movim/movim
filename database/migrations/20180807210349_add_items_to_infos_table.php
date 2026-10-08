<?php

use Movim\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddItemsToInfosTable extends Migration
{
    public function up()
    {
        $this->schema->table('infos', function (Blueprint $table) {
            $table->integer('items')->default(0);
        });
    }

    public function down()
    {
        $this->schema->table('infos', function (Blueprint $table) {
            $table->dropColumn('items');
        });
    }
}
