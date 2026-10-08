<?php

use Illuminate\Database\Schema\Blueprint;
use Movim\Migration;

class RemoveThemeFromConfigurationTable extends Migration
{
    public function up()
    {
        $this->schema->table('configuration', function (Blueprint $table) {
            $table->dropColumn('theme');
        });
    }

    public function down()
    {
        $this->schema->table('configuration', function (Blueprint $table) {
            $table->string('theme')->default('material');
        });
    }
}
