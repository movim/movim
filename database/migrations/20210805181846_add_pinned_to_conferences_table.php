<?php

use Movim\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddPinnedToConferencesTable extends Migration
{
    public function up()
    {
        $this->schema->table('conferences', function (Blueprint $table) {
            $table->boolean('pinned')->default(false);
        });
    }

    public function down()
    {
        $this->schema->table('conferences', function (Blueprint $table) {
            $table->dropColumn('pinned');
        });
    }
}