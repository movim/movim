<?php

use Movim\Migration;
use Illuminate\Database\Schema\Blueprint;

class ChangeNameTypeConferencesTable extends Migration
{
    public function up()
    {
        $this->schema->table('conferences', function (Blueprint $table) {
            $table->text('name')->change();
        });
    }

    public function down()
    {
        $this->schema->table('conferences', function (Blueprint $table) {
            $table->string('name', 512)->change();
        });
    }
}
