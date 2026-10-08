<?php

use Illuminate\Database\Schema\Blueprint;
use Movim\Migration;

class AddCallToConferencesTable extends Migration
{
    public function up()
    {
        $this->schema->table('conferences', function (Blueprint $table) {
            $table->boolean('call')->default(false);
        });
    }

    public function down()
    {
        $this->schema->table('conferences', function (Blueprint $table) {
            $table->dropColumn('call');
        });
    }
}
