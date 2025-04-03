<?php

use Movim\Migration;
use Illuminate\Database\Schema\Blueprint;

class RemoveMoodContactsTable extends Migration
{
    public function up()
    {
        $this->schema->table('contacts', function (Blueprint $table) {
            $table->dropColumn('mood');
        });
    }

    public function down()
    {
        $this->schema->table('contacts', function (Blueprint $table) {
            $table->string('mood', 32)->nullable();
        });
    }
}
