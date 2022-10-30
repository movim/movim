<?php

use Movim\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddPhoneToContactsTable extends Migration
{
    public function up()
    {
        $this->schema->table('contacts', function (Blueprint $table) {
            $table->string('phone')->nullable();
        });
    }

    public function down()
    {
        $this->schema->table('contacts', function (Blueprint $table) {
            $table->dropColumn('phone');
        });
    }
}
