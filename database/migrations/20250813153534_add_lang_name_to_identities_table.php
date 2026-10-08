<?php

use Illuminate\Database\Schema\Blueprint;
use Movim\Migration;

class AddLangNameToIdentitiesTable extends Migration
{
    public function up()
    {
        $this->schema->table('identities', function (Blueprint $table) {
            $table->string('lang')->nullable();
            $table->string('name')->nullable();
        });
    }

    public function down()
    {
        $this->schema->table('identities', function (Blueprint $table) {
            $table->dropColumn('lang');
            $table->dropColumn('name');
        });
    }
}
