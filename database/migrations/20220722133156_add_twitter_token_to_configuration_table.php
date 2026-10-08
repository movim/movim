<?php

use Illuminate\Database\Schema\Blueprint;
use Movim\Migration;

class AddTwitterTokenToConfigurationTable extends Migration
{
    public function up()
    {
        $this->schema->table('configuration', function (Blueprint $table) {
            $table->string('twittertoken')->nullable();
        });
    }

    public function down()
    {
        $this->schema->table('configuration', function (Blueprint $table) {
            $table->dropColumn('twittertoken');
        });
    }
}
