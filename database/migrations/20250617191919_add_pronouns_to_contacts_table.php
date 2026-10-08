<?php

use Movim\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddPronounsToContactsTable extends Migration
{
    public function up()
    {
        $this->schema->table('contacts', function (Blueprint $table) {
            $table->string('pronouns')->nullable();
        });
    }

    public function down()
    {
        $this->schema->table('contacts', function (Blueprint $table) {
            $table->dropColumn('pronouns');
        });
    }
}
