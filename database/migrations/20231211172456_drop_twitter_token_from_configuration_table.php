<?php

use Movim\Migration;
use Illuminate\Database\Schema\Blueprint;

class DropTwitterTokenFromConfigurationTable extends Migration
{
    public function up()
    {
        $this->schema->table('configuration', function (Blueprint $table) {
            $table->dropColumn('twittertoken');
        });
    }

    public function down()
    {
        $this->schema->table('configuration', function (Blueprint $table) {
            $table->string('twittertoken')->nullable();
        });
    }
}
