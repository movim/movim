<?php

use Movim\Migration;
use Illuminate\Database\Schema\Blueprint;

class RemoveXmppCountryFromConfigurationTable extends Migration
{
    public function up()
    {
        $this->schema->table('configuration', function (Blueprint $table) {
            $table->dropColumn('xmppcountry');
        });
    }

    public function down()
    {
        $this->schema->table('configuration', function (Blueprint $table) {
            $table->string('xmppcountry')->nullable();
        });
    }
}
