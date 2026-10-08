<?php

use Movim\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddGifapikeyToConfigurationTable extends Migration
{
    public function up()
    {
        $this->schema->table('configuration', function (Blueprint $table) {
            $table->string('gifapikey')->nullable();
        });
    }

    public function down()
    {
        $this->schema->table('configuration', function (Blueprint $table) {
            $table->dropColumn('gifapikey');
        });
    }
}
