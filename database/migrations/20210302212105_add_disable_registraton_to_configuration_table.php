<?php

use Illuminate\Database\Schema\Blueprint;
use Movim\Migration;

class AddDisableRegistratonToConfigurationTable extends Migration
{
    public function up()
    {
        $this->schema->table('configuration', function (Blueprint $table) {
            $table->boolean('disableregistration')->default(false);
        });
    }

    public function down()
    {
        $this->schema->table('configuration', function (Blueprint $table) {
            $table->dropColumn('disableregistration');
        });
    }
}
